<?php

class Age extends Simpla
{
    /**
     * @param $scoring_id
     * @return array|null
     */
    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        if (empty($scoring))
            return null;

        $result = $this->run($scoring);
        if (!empty($result)) {
            if (!empty($result['status']) && in_array($result['status'], [
                    $this->scorings::STATUS_COMPLETED,
                    $this->scorings::STATUS_ERROR,
                ]))
                $result['end_date'] = date('Y-m-d H:i:s');

            $this->scorings->update_scoring($scoring_id, $result);
        }
        return $result;
    }

    /**
     * @param ArrayObject $scoring
     * @return array
     */
    function run($scoring)
    {
        $user = $this->users->get_user($scoring->user_id);
        if (empty($user)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Пользователь не найден'
            ];
        }

        if (empty($user->birth)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'в заявке не указана дата рождения'
            ];
        }

        $age = date_diff(date_create(date('Y-m-d', strtotime($user->birth))), date_create(date('Y-m-d')));
        $age_year = $age->y;

        $scoring_type = $this->scorings->get_type($this->scorings::TYPE_AGE);
        if (($scoring_type->params['max_age'] <= $age_year) && ($scoring_type->params['max_age_pk'] >= $age_year)) {
            return [
                'status' => $this->scorings::STATUS_COMPLETED,
                'body' => 'Возраст: ' . $age_year,
                'string_result' => 'Допустимый возраст: ' . $age_year,
                'success' => 1
            ];
        }

        // Отказ по заявке, если есть
        if (!empty($scoring->order_id) && $order = $this->orders->get_order((int)$scoring->order_id))
        {
            // техаккаунт System
            $tech_manager = $this->managers->get_manager(50);

            $update_order = [
                'status' => 3,
                'manager_id' => $tech_manager->id,
                'reason_id' => 23, // Менее 21 года
                'reject_date' => date('Y-m-d H:i:s')
            ];
            $this->orders->update_order($scoring->order_id, $update_order);
            $this->leadgid->reject_actions($scoring->order_id);

            $this->virtualCard->forUser($order->user_id)->delete();

            $changeLogs = Helpers::getChangeLogs($update_order, $order);
            $this->changelogs->add_changelog([
                'manager_id' => $tech_manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'status',
                'old_values' => serialize($changeLogs['old']),
                'new_values' => serialize($changeLogs['new']),
                'order_id' => $order->order_id,
                'user_id' => $order->user_id,
            ]);

            $reason = $this->reasons->get_reason(23);
            $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $tech_manager->name_1c, 0, 1, $reason->admin_name);

            if (!empty($order->is_user_credit_doctor))
                $this->soap1c->send_credit_doctor($order->id_1c);

            $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

            $this->soap->block_order_1c($order->id_1c, 0);

            // отправляем заявку на кредитного доктора
            $this->cdoctor->send_order($order->order_id);

            // Останавливаем выполнения других скорингов по этой заявки
            $this->scorings->stopOrderScorings($order->order_id, ['string_result' => 'Причина: скоринг ' . $scoring_type->title]);
        }

        return [
            'status' => $this->scorings::STATUS_COMPLETED,
            'body' => 'Возраст: ' . $age_year,
            'string_result' => 'Недопустимый возраст: ' . $age_year,
            'success' => 0
        ];
    }
}