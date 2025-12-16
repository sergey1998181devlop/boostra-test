<?php
require_once 'Simpla.php';

class Autoconfirm extends Simpla
{
    /**
     * Проверяет включение автоподтверждения с проверкой безопасного флоу
     *
     * @param object $user Объект пользователя
     * @return bool true если включено с учетом safety flow и cookie
     */
    public function is_enabled($user)
    {
        $is_test_user = $this->user_data->read($user->id, $this->user_data::TEST_USER);
        if ($is_test_user || $this->settings->autoconfirm_enabled) {
            return empty($_COOKIE['autoconfirm_disabled']) && !$this->users->isSafetyFlow($user);
        }
        
        return false;
    }

    /**
     * Проверяет включение автоподтверждения БЕЗ проверки безопасного флоу
     *
     * @param object $user Объект пользователя
     * @return bool true если пользователь тестовый или включено автоподтверждение
     */
    public function is_enabled_without_safety_flow(object $user): bool
    {
        $is_test_user = $this->user_data->read($user->id, $this->user_data::TEST_USER);
        return $is_test_user || $this->settings->autoconfirm_enabled;
    }

    /**
     * Проверяет включение автоподтверждения в зависимости от активного флоу
     *
     * @param object $user Объект пользователя
     * @param string|null $activeAutoConfirmFlow Активный флоу автоподтверждения
     * @return bool true если автоподтверждение включено для данного флоу
     */
    public function is_enabled_by_flow(object $user, ?string $activeAutoConfirmFlow = null): bool
    {
        // Если флоу пустой, получаем его из БД
        if (empty($activeAutoConfirmFlow)) {
            $activeAutoConfirmFlow = $this->user_data->read($user->id, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW);
        }
        
        if ($activeAutoConfirmFlow === $this->user_data::ACTIVE_AUTOCONFIRM_2_FLOW_VALUE) {
            return $this->is_enabled_without_safety_flow($user);
        } else {
            return $this->is_enabled($user);
        }
    }

    /**
     * Получает сумму для дальнейшего автоодобрения
     * @param int $user_id
     * @param object $order
     * @return int
     */
    public function getAutoConfirmAmount(int $user_id, object $order): int
    {
        $order_id = $order->id;

        // Проверем есть ли одобренная сумма скорингом
        $decisionSum = $this->scorings->getApproveAmountScoring($user_id);
        $user_amount = (int)$this->order_data->read($order_id, $this->order_data::USER_AMOUNT);

        // Если нет скоринга, но заявка одобрена, берем сумму из заявки
        if (empty($decisionSum) && $order->status == Orders::ORDER_STATUS_CRM_APPROVED) {
            $decisionSum =  $order->amount;
        }

        // Когда нет суммы возвращаем 0 или запрошенная сумма < 5000
        if (empty($decisionSum) || $user_amount < 5000) {
            return 0;
        }

        // Если запрошенная сумма больше или равна одобренной, ставим автоматическую авто выдачу
        if ($user_amount >= $decisionSum) {
            return $decisionSum;
        }

        return $this->gradationNewClient($user_amount, $decisionSum);
    }

    /**
     * Сумма для НК из таблицы
     * @param int $user_amount
     * @param int $approve_amount
     * @return void
     */
    private function gradationNewClient(int $user_amount, int $approve_amount): int
    {
        if ($user_amount === 5000 && $approve_amount <= 6000) {
            return 6000;
        } elseif ($user_amount === 6000 && $approve_amount <= 7000) {
            return 7000;
        } elseif ($user_amount === 7000 && $approve_amount <= 8000) {
            return 8000;
        } elseif ($user_amount === 8000 && $approve_amount <= 9000) {
            return 9000;
        } elseif ($user_amount === 9000 && $approve_amount <= 10000) {
            return 10000;
        } elseif ($user_amount === 10000 && $approve_amount <= 11000) {
            return 11000;
        } elseif ($user_amount === 11000 && $approve_amount <= 12000) {
            return 12000;
        } elseif ($user_amount === 12000 && $approve_amount <= 14000) {
            return 14000;
        } elseif ($user_amount === 13000 && $approve_amount <= 15000) {
            return 15000;
        } elseif ($user_amount === 14000 && $approve_amount <= 16000) {
            return 16000;
        } elseif ($user_amount === 15000 && $approve_amount <= 17000) {
            return 17000;
        } elseif ($user_amount === 16000 && $approve_amount <= 18000) {
            return 18000;
        } elseif ($user_amount === 17000 && $approve_amount <= 19000) {
            return 19000;
        } elseif ($user_amount === 18000 && $approve_amount <= 20000) {
            return 20000;
        } elseif ($user_amount === 19000 && $approve_amount <= 21000) {
            return 21000;
        } elseif ($user_amount === 20000 && $approve_amount <= 22000) {
            return 22000;
        } elseif ($user_amount === 21000 && $approve_amount <= 23000) {
            return 23000;
        } elseif ($user_amount === 22000 && $approve_amount <= 25000) {
            return 25000;
        } elseif ($user_amount === 23000 && $approve_amount <= 26000) {
            return 26000;
        } elseif ($user_amount === 24000 && $approve_amount <= 27000) {
            return 27000;
        } elseif ($user_amount === 25000 && $approve_amount <= 28000) {
            return 28000;
        } elseif ($user_amount === 26000 && $approve_amount <= 29000) {
            return 29000;
        } elseif ($user_amount === 27000 && $approve_amount <= 30000) {
            return 30000;
        } elseif ($user_amount === 28000 && $approve_amount <= 30000) {
            return 30000;
        } elseif ($user_amount === 29000 && $approve_amount <= 30000) {
            return 30000;
        } elseif ($user_amount === 30000 && $approve_amount <= 30000) {
            return 30000;
        } else {
            return 0;
        }
    }

    /**
     * Добавляет заявку в очередь на автоподписание
     *
     * @param $autoconfirm_amount
     * @param $order
     * @return void
     */
    public function set_autoconfirm($autoconfirm_amount, $order)
    {
        if ($autoconfirm_amount > 0) {
            $update = [
                'amount' => $autoconfirm_amount,
                'status' => $this->orders::ORDER_STATUS_CRM_AUTOCONFIRM,
            ];
            $this->orders->update_order($order->id, $update);

            $this->changelogs->add_changelog(
                [
                    'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'autoconfirm',
                    'old_values' => serialize(
                        [
                            'amount' => $order->amount,
                            'status' => $order->status,
                        ]
                    ),
                    'new_values' => serialize($update),
                    'order_id' => $order->id,
                ]
            );
        }
    }
}