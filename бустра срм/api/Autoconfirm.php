<?php

require_once 'Simpla.php';

class Autoconfirm extends Simpla
{
    public function set_autoconfirm($autoconfirm_amount, $order)
    {
        if ($autoconfirm_amount > 0) {
            $update = [
                'amount' => $autoconfirm_amount,
                'status' => $this->orders::ORDER_STATUS_CRM_AUTOCONFIRM,
            ];
            $this->orders->update_order($order->order_id, $update);

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'autoconfirm',
                'old_values' => serialize([
                    'amount' => $order->amount,
                    'status' => $order->status,
                ]),
                'new_values' => serialize($update),
                'order_id' => $order->order_id,
            ));
        }
    }

    /**
     * Autoconfirm::get_amount()
     * В случае успеха возвращает сумму возможного автоподписания,
     * или 0, если автоподписание не возможно
     * @param stdClass $order
     * @return int
     */
    public function get_amount($order): int
    {
        $order_id = $order->order_id ?? $order->id;
        $user_id = $order->user_id;

        $user = $this->users->get_user($user_id);
        $this->settings->setSiteId($user->site_id);

        // Проверка белого списка - запрет автоподписания для высокорисковых клиентов
        if ($this->user_data->read($user_id, $this->user_data::WHITELIST_DOP)) {
            return 0;
        }

        // Если это автозаявка, то убираем автоподписание
        if ($order->utm_source === $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE) {
            return 0;
        }

        // Если это кросордер, то убираем автоподписание
        if ($order->utm_source === $this->orders::UTM_SOURCE_CROSS_ORDER) {
            return 0;
        }

        // Убираем автоподписание для ВКЛ
        if ($this->order_data->read($order_id, $this->order_data::RCL_LOAN)) {
            return 0;
        }

    	if ($this->availableGetAmount($order)) {
            $autoconfirm_asp = $this->order_data->get($order_id, $this->order_data::AUTOCONFIRM_ASP);
            if (!empty($autoconfirm_asp)) {
                if ($order->loan_type == Orders::LOAN_TYPE_PDL) {
                    return $this->scorings->getApproveAmountScoring($user_id, $order_id);
                } elseif ($order->loan_type == Orders::LOAN_TYPE_IL) {
                    $approveAmount = $this->scorings->getApproveILAmountScoring($user_id);
                    return min($approveAmount, Orders::IL_MAX_AUTO_CONFIRM_AMOUNT_NK);
                }
            }
    	}

        return 0;
    }

    /**
     * Проверка на возможность получения суммы из скорингов
     *
     * Выдаем пдл + пк или нк, ил + только нк
     *
     * @param stdClass $order
     * @return bool
     */
    private function availableGetAmount(stdClass $order): bool
    {
        return $this->is_enabled($order->user_id)
            && ($order->loan_type == Orders::LOAN_TYPE_PDL
                || ($order->loan_type == Orders::LOAN_TYPE_IL && empty($order->have_close_credits))
            );
    }

    public function is_enabled($user_id): bool
    {
        return $this->settings->autoconfirm_enabled || $this->user_data->read($user_id, $this->user_data::TEST_USER);
    }

}