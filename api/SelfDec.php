<?php

require_once 'Simpla.php';

/**
 * Класс проверяет наличие самозапрета перед выдачей займа
 */
class SelfDec extends Simpla
{
    /** @var string Максимальное время, до которого можно проверять самозапрет по скорингу проверки отчетов для безопасности */
    private const MAX_TIME_TO_CHECK_SELF_DEC_BY_REPORT_SCORINGS = '23:55:00';

    /** @var int Кол-во секунд для генерации нового applicationId для акси */
    private const SECONDS_TO_REQUEST_NEW_APPLICATION_ID = 180;

    private const LOG_FILE = 'self_dec.txt';

    /** @var string Есть самозапрет, не выдаем */
    public const DECLINE_DECISION = 'Decline';

    /** @var string Не получено решение, есть ли самозапрет (показываем ошибку) */
    public const NO_DECISION = 'No_decision';

    /** @var string Нет самозапрета, выдаем */
    public const APPROVE_DECISION = 'Approve';

    /** @var string Выключена проверка самозапрета перед выдачей, разрешаем выдать */
    public const DISABLED_DECISION = 'Disabled_decision';

    public function getUserSelfDecDecision(int $orderId): string
    {
        $orderCrm = $this->orders->get_crm_order($orderId);

        $curTime = date('H:i:s');

        // Если текущее время меньше установленного максимума
        if ($curTime < self::MAX_TIME_TO_CHECK_SELF_DEC_BY_REPORT_SCORINGS) {

            // 1. Проверить, есть ли решение о самозапрете за сегодня
            $selfDecDecision = $this->getPreviousSelfDecDecisionForToday((int)$orderCrm->order_id);
            if (in_array($selfDecDecision, [$this->self_dec::DECLINE_DECISION, $this->self_dec::APPROVE_DECISION])) {
                return $selfDecDecision;
            }

            // 2. Проверить, есть ли самозапрет согласно скорингам акси
            $result = $this->checkHasUserSelfDecByReportScorings($orderCrm);

            if ($result === self::APPROVE_DECISION) {
                return self::APPROVE_DECISION;
            }
        }

        // 3. Проверить, есть ли самозапрет, отправив запрос в акси
        return $this->orders->isCrossOrder($orderCrm) ?
            $this->checkHasUserSelfDecByNewRequestToAxiForCrossOrder($orderCrm) :
            $this->checkHasUserSelfDecByNewRequestToAxiForMainOrder($orderCrm);
    }

    /**
     * Получить решение за сегодня
     *
     * @param int $orderId
     * @return string|null
     */
    private function getPreviousSelfDecDecisionForToday(int $orderId): ?string
    {
        $selfDecDecision = $this->order_data->get($orderId, $this->order_data::SELF_DEC_DECISION);

        if (!empty($selfDecDecision)) {
            $curDate = date('Y-m-d');
            $selfDecDecisionDate = date('Y-m-d', strtotime($selfDecDecision->updated));

            if ($selfDecDecisionDate === $curDate) {
                return $selfDecDecision->value;
            }
        }

        return null;
    }

    /**
     * @param stdClass $orderCrm
     * @return string
     */
    private function checkHasUserSelfDecByReportScorings(stdClass $orderCrm): string
    {
        $axiScoringType = $this->axi->getAxiScoringType($orderCrm);

        $scorings = $this->scorings->get_scorings([
            'user_id' => (int)$orderCrm->user_id,
            'type' => $axiScoringType,
            'status' => $this->scorings::STATUS_COMPLETED,
        ]);

        $curDate = date('Y-m-d');
        foreach ($scorings as $scoring) {
            $scoringDate = date('Y-m-d', strtotime($scoring->created));

            // Если за сегодня есть успешный акси значит самозапрета нет
            if ($scoringDate === $curDate && !empty($scoring->success)) {
                $this->logging(__METHOD__, '', ['order_id' => $orderCrm->order_id],
                    'Нет самозапрета у заявки согласно скорингу акси c id ' . $scoring->id, self::LOG_FILE);
                return self::APPROVE_DECISION;
            }
        }

        return self::NO_DECISION;
    }

    /**
     * @param stdClass $crossOrder
     * @return string
     */
    private function checkHasUserSelfDecByNewRequestToAxiForCrossOrder(stdClass $crossOrder): string
    {
        // Получаем оригинальную заявку, из которой был создан кросс-ордер
        $mainOrderId = $crossOrder->utm_medium;
        if (!empty($mainOrderId)) {
            $mainOrder = $this->orders->get_crm_order($mainOrderId);
        }

        // Если оригинальная заявка не найдена, то ищем последнюю заявку клиента
        if (empty($mainOrder)) {
            $userOrders = $this->orders->get_crm_orders([
                'user_id' => (int)$crossOrder->user_id,
                'sort' => 'order_id_desc'
            ]);

            foreach ($userOrders as $userOrder) {
                if (!$this->orders->isCrossOrder($userOrder)) {
                    $mainOrder = $userOrder;
                    break;
                }
            }

            if (empty($mainOrder)) {
                $this->logging(__METHOD__, '', ['order_id' => $crossOrder->order_id],
                    'Не найдена основная заявка для кросс-ордера ' . $crossOrder->order_id . ', поэтому проверка на самозапрет не проводилась!', self::LOG_FILE);
                return self::NO_DECISION;
            }
        }

        $this->logging(__METHOD__, '', ['order_id' => $crossOrder->order_id],
            'Для получении информации о самозапрете заявки кросс-ордера ' . $crossOrder->order_id . ' используем заявку клиента ' . $mainOrder->order_id, self::LOG_FILE);

        // Проверяем, есть ли решение о самозапрете за сегодня по оригинальной заявке
        $selfDecDecision = $this->getPreviousSelfDecDecisionForToday($mainOrder->order_id);
        if (in_array($selfDecDecision, [$this->self_dec::DECLINE_DECISION, $this->self_dec::APPROVE_DECISION])) {
            return $selfDecDecision;
        }

        return $this->checkHasUserSelfDecByNewRequestToAxiForMainOrder($mainOrder);
    }

    /**
     * @param stdClass $order
     * @return string
     */
    private function checkHasUserSelfDecByNewRequestToAxiForMainOrder(stdClass $order): string
    {
        $selfDecAxiApplicationId = $this->order_data->get((int)$order->order_id, $this->order_data::SELF_DEC_AXI_APPLICATION_ID);

        $prefix = 'SSP_';

        // Если первый запрос, то отправляем запрос в акси
        if (empty($selfDecAxiApplicationId)) {
            $applicationId = $prefix . '0' . $order->order_id;
            $this->axi->createSelfDecApplication($order, $applicationId);
            $this->order_data->set((int)$order->order_id, $this->order_data::SELF_DEC_AXI_APPLICATION_ID, $applicationId);

            // Ждем, т.к. проверка в акси наличия у клиента самозапрета отрабатывает не сразу
            sleep(3);
        } // Если прошло меньше установленного времени, то получаем результат акси по старому applicationId
        elseif (time() - strtotime($selfDecAxiApplicationId->updated) < self::SECONDS_TO_REQUEST_NEW_APPLICATION_ID) {
            $applicationId = $selfDecAxiApplicationId->value;
        } // Если прошло больше установленного времени, то получаем новый applicationId для акси и отправляем запрос в акси
        else {
            $digitAfterPrefix = (int)substr(str_replace($prefix, '', $selfDecAxiApplicationId->value), 0, 1);
            $applicationId = $prefix . ($digitAfterPrefix + 1) . $order->order_id;
            $this->axi->createSelfDecApplication($order, $applicationId);
            $this->order_data->set((int)$order->order_id, $this->order_data::SELF_DEC_AXI_APPLICATION_ID, $applicationId);

            // Ждем, т.к. проверка в акси наличия у клиента самозапрета отрабатывает не сразу
            sleep(3);
        }

        // Перезапрашиваем результат акси в течение 30 сек
        for ($i = 1; $i <= 10; $i++) {
            $result = $this->axi->getApplication($applicationId);

            if (empty($result)) {
                $this->logging(__METHOD__, '', 'Не получен результат акси о том, есть ли самозапрет, поэтому проверка на самозапрет не проводилась!', ['order_id' => $order->order_id, 'result' => $result], self::LOG_FILE);
                return self::NO_DECISION;
            }

            $result = json_decode($result);

            if (empty($result) || empty($result->Application->AXI->application_e->decision_e)) {
                $this->logging(__METHOD__, '', 'Некорректный результат акси о том, есть ли самозапрет, поэтому проверка на самозапрет не проводилась!', ['order_id' => $order->order_id, 'result' => $result], self::LOG_FILE);
                return self::NO_DECISION;
            }

            $finalDecision = $result->Application->AXI->application_e->decision_e->{'@final_decision'};

            // Получили окончательный ответ, выходим из цикла
            if (!empty($finalDecision)) {
                break;
            }

            sleep(3);
        }

        $stopFactors = $result->Application->AXI->application_e->policyRules->{'@stop_factors'};

        $hasUserSelfDec = false;

        if (empty($finalDecision)) {
            $this->logging(__METHOD__, '', 'Не получен ответ из акси по наличию самозапрета', ['order_id' => $order->order_id, 'finalDecision' => $finalDecision], self::LOG_FILE);
            return self::NO_DECISION;
        }

        if ($finalDecision === $this->axi::FINAL_DECISION_DECLINE && mb_strpos($stopFactors, 'SSP_SELFDEC') !== false) {
            $hasUserSelfDec = true;
        }

        $dataToLog = [
            'order_id' => $order->order_id,
            'applicationId' => $applicationId,
            'finalDecision' => $finalDecision,
            'stopFactors' => $stopFactors,
            'result' => $hasUserSelfDec,
            'Кол-во запросов к акси' => $i,
        ];

        $this->logging(__METHOD__, '', 'Результат проверки на самозапрет: ', $dataToLog, self::LOG_FILE);

        return $hasUserSelfDec ? self::DECLINE_DECISION : self::APPROVE_DECISION;
    }

    public function rejectOrder(int $order_id): void
    {
        if (empty($order_id)) {
            $this->logging(__METHOD__, '', ['order_id' => $order_id],
                'Заявка не найден', self::LOG_FILE);
            return;
        }

        $order = $this->orders->get_order($order_id);

        $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

        $reason_id = $this->reasons::REASON_SELF_DEC;

        $update_order = [
            'status' => $this->orders::STATUS_REJECTED,
            'manager_id' => $tech_manager->id,
            'reason_id' => $reason_id,
            'reject_date' => date('Y-m-d H:i:s')
        ];
        $this->orders->update_order($order_id, $update_order);

        $this->logging(__METHOD__, '', ['order_id' => $order_id], 'Заявка переведена в отказ', self::LOG_FILE);

        $this->leadgid->reject_actions($order_id);

        $changeLogs = Helpers::getChangeLogs($update_order, $order);
        $this->changelogs->add_changelog([
            'manager_id' => $tech_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($changeLogs['old']),
            'new_values' => serialize($changeLogs['new']),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ]);

        $reason = $this->reasons->get_reason($reason_id);
        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED_FOR_SEND, $tech_manager->name_1c, 0, 1, $reason->admin_name);

        if (!empty($order->is_user_credit_doctor))
            $this->soap->send_credit_doctor($order->id_1c);

        $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

        // отправляем заявку на кредитного доктора
        $this->cdoctor->send_order($order_id);
    }
}
