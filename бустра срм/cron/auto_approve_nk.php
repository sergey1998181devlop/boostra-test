<?php

use App\Enums\AutoApproveOrders;

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '1200');
ini_set('memory_limit', '4096M');

require_once dirname(__FILE__) . '/../api/Simpla.php';
require_once dirname(__FILE__) . '/../api/Scorings.php';

/**
 * Проверяет и отрабатывает очередь CRON на добавление автоодобрений
 * Class ValidateAutoApproveOrders
 */
class AutoApproveNK extends Simpla
{
    private const LOG_FILE = 'auto_approve_nk.txt';

    /** @var int Сколько последних заявок клиента мы берем для проверки, есть ли среди них открытый */
    private const LAST_ORDERS_AMOUNT_FOR_OPEN_ORDER_CHECK = 3;

    public function run()
    {
        $this->logging(__METHOD__, '', '', 'Начало работы крона', self::LOG_FILE);

        $settings_auto_approve = $this->settings->auto_approve;

        if (empty($settings_auto_approve['status_nk'])) {
            $this->logging(__METHOD__, '', 'Нет настроек. Завершение работы крона', ['settings_auto_approve' => $settings_auto_approve], self::LOG_FILE);
            return;
        }

        $tasks = $this->orders_auto_approve->getAutoApprovesNK([
            'statuses' => [
                $this->orders_auto_approve::STATUS_CRON_NEW
            ]
        ]);

        if (empty($tasks)) {
            $this->logging(__METHOD__, '', 'Нет записей. Завершение работы крона', ['tasks' => $tasks], self::LOG_FILE);
            return;
        }

        foreach ($tasks as $task) {
            $this->updateAutoApproveNK((int)$task->id, [
                'status' => $this->orders_auto_approve::STATUS_CRON_PROCESS
            ]);
        }

        foreach ($tasks as $task) {
            try {
                $result = $this->tryGenerateAutoApproveOrder($task);
            } catch (Throwable $error) {
                $this->logging(__METHOD__, '', 'Ошибка при попытке создания автозаявки', ['task' => $task, 'error' => $error], self::LOG_FILE);

                $result = [
                    'status' => $this->orders_auto_approve::STATUS_CRON_NEW,
                    'reason' => AutoApproveOrders::REASON_UNEXPECTED_ERROR_REASON
                ];

                // Если прошло больше установленного времени, то переводим в ошибку
                if ($this->orders_auto_approve->isTimeout((int)strtotime($task->date_added))) {
                    $result['status'] = $this->orders_auto_approve::STATUS_CRON_ERROR_TIMEOUT;
                }
            }

            $this->updateAutoApproveNK((int)$task->id, [
                'status' => $result['status'],
                'reason' => $result['reason']
            ]);

            $task->new_status = $result['status'];
            $task->new_reason = $result['reason'];
        }

        $this->logging(__METHOD__, '', '', 'Завершение работы крона', self::LOG_FILE);
    }

    /**
     * Попытаться создать автозаявку
     *
     * @param stdClass $task
     * @return array
     */
    private function tryGenerateAutoApproveOrder(stdClass $task): array
    {
        $this->logging(__METHOD__, '', 'Начата попытка генерации автозаявки', ['task' => $task], self::LOG_FILE);

        $user = $this->users->get_user((int)$task->user_id);

        // 1. Валидация юзера
        $userValidationResult = $this->validateUser($user);
        if (empty($userValidationResult['success'])) {
            return ['status' => $this->orders_auto_approve::STATUS_CRON_ERROR_VALIDATE, 'reason' => $userValidationResult['reason']];
        }

        // Производим валидацию по последней заявке клиента (в validateUser обновляется s_orders.`1c_status` вызовом $this->soap->check_order_1c)
        $lastOrder = $this->orders->get_user_last_order((int)$task->user_id);

        $this->logging(__METHOD__, '', 'Производим валидацию по последней заявке клиента', ['last_order' => $lastOrder], self::LOG_FILE);

        // 2. Валидация заявки
        $orderValidationResult = $this->validateOrder($lastOrder);
        if (empty($orderValidationResult['success'])) {
            return ['status' => $this->orders_auto_approve::STATUS_CRON_ERROR_VALIDATE, 'reason' => $orderValidationResult['reason']];
        }

        // 3. Валидация карты
        $cardValidationResult = $this->validateCard($lastOrder);
        if (empty($cardValidationResult['success'])) {
            return ['status' => $this->orders_auto_approve::STATUS_CRON_ERROR_CREDIT_CARD, 'reason' => $cardValidationResult['reason']];
        }

        // 4. Валидация скорингов
        $scoringsValidationResult = $this->validateScorings($task);
        if (empty($scoringsValidationResult['success'])) {
            return ['status' => $this->orders_auto_approve::STATUS_CRON_ERROR_SCORING, 'reason' => $scoringsValidationResult['reason']];
        }

        // 5. Создание автозаявки
        $createAutoApproveOrderResult = $this->orders_auto_approve->createAutoApproveOrder($lastOrder, $user, $cardValidationResult['card_id'], $cardValidationResult['card_type']);

        if (empty($createAutoApproveOrderResult['success'])) {
            // 6. Если ошибка при добавлении заявки в 1С и прошло меньше установленного времени, то переводим в статус Новая,
            // чтобы следующий крон заново попытался создать автозаявку
            if (
                $createAutoApproveOrderResult['reason'] === AutoApproveOrders::REASON_ORDER_NOT_ADDED_TO_1C &&
                !$this->orders_auto_approve->isTimeout((int)strtotime($task->date_added))
            ) {
                $this->logging(__METHOD__, '', 'Повторная попытка добавления заявки в 1C', ['user' => $user], self::LOG_FILE);
                return ['status' => $this->orders_auto_approve::STATUS_NEW, 'reason' => AutoApproveOrders::RESEND_ORDER_TO_1C];
            }

            // 7. Другая причина несоздания автозаявки
            return ['status' => $this->orders_auto_approve::STATUS_CRON_ERROR_GENERATE, 'reason' => $createAutoApproveOrderResult['reason']];
        }

        return ['status' => $this->orders_auto_approve::STATUS_CRON_SUCCESS, 'reason' => $createAutoApproveOrderResult['reason']];
    }

    /** 1. Валидация юзера */
    private function validateUser($user): array
    {
        // 1. Пользователь не найден
        if (empty($user)) {
            $this->logging(__METHOD__, '', 'Пользователь не найден', ['user' => $user], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_NO_USER];
        }

        // 2. У клиента мораторий на создание заявок
        if (!empty($user->maratorium_date) && $user->maratorium_date >= date('Y-m-d H:i:s')) {
            $this->logging(__METHOD__, '', 'У клиента мораторий на создание заявок', ['user' => $user], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_USER_HAS_MORATORIUM];
        }

        // 3. ЛК клиента заблокирован
        if (!empty($user->blocked)) {
            $this->logging(__METHOD__, '', 'ЛК клиента заблокирован', ['user' => $user], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_USER_IS_BLOCKED];
        }

        $result = $this->soap->get_uid_by_phone($user->phone_mobile);

        // 4. ЛК клиента удален в 1C
        if (!empty($result->error) && $result->error === 'ЛК удален') {
            $this->logging(__METHOD__, '', 'ЛК клиента заблокирован в 1C', ['user' => $user, 'result' => $result], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_USER_IS_BLOCKED_IN_1C];
        }

        $orders = $this->orders->get_orders([
            'user_id' => (int)$user->id,
            'sort' => 'order_id_desc'
        ]);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', 'У клиента нет заявок', ['orders' => $orders], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_NO_ORDER];
        }

        $this->logging(__METHOD__, '', 'На проверку открытых заявок взяты заявки', ['orders_id' => array_column($orders, 'order_id')], self::LOG_FILE);

        // 5. Если у клиента среди n-последних заявок есть открытый займ (исключая кросс-ордера) (предварительно актуализируем 1c_status из 1c)
        $ordersToUpdate1CStatus = [];
        $i = 0;
        foreach ($orders as $order) {

            if ($this->orders->isCrossOrder($order)) {
                continue;
            }

            $i++;

            if ($i > self::LAST_ORDERS_AMOUNT_FOR_OPEN_ORDER_CHECK) {
                break;
            }

            if (!$this->orders->isOrderClosed($order)) {
                $ordersToUpdate1CStatus[] = $order;
            }
        }

        if (!empty($ordersToUpdate1CStatus)) {

            if ($this->orders->checkAreOrdersClosedWithUpdate1cStatus($ordersToUpdate1CStatus)) {
                // Заново получаем заявки, т.к. статус мог измениться
                $orders = $this->orders->get_orders([
                    'user_id' => (int)$user->id,
                    'sort' => 'order_id_desc'
                ]);
            } else {
                return ['success' => false, 'reason' => AutoApproveOrders::REASON_USER_HAS_OPEN_ORDER];
            }
        }

        $closedOrdersDuringYearInBaseOrganization = [];
        $dateStart = date('Y-m-d H:i:s', strtotime('-1 year'));

        foreach ($orders as $order) {
            if (
                $order->status_1c === $this->orders::ORDER_1C_STATUS_CLOSED &&
                in_array($order->organization_id, [$this->organizations::RZS_ID, $this->organizations::FRIDA_ID]) &&
                $order->date >= $dateStart
            ) {
                $closedOrdersDuringYearInBaseOrganization[] = $order;
            }
        }

        // 6. Превышено максимальное кол-во закрытых заявок за год в текущей МКК
        if (count($closedOrdersDuringYearInBaseOrganization) >= $this->orders_auto_approve::MAX_CLOSED_ORDERS_AMOUNT_PER_YEAR) {
            $this->logging(__METHOD__, '', 'Превышено максимальное кол-во заявок за год', ['user' => $user, 'orders_amount' => count($closedOrdersDuringYearInBaseOrganization)], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_USER_HAS_EXCESSED_MAX_ORDERS_AMOUNT];
        }

        return ['success' => true];
    }

    /** 2. Валидация заявки */
    public function validateOrder($lastOrder): array
    {
        // 1. Заявка не найдена
        if (empty($lastOrder)) {
            $this->logging(__METHOD__, '', 'Заявка не найдена', ['last_order' => $lastOrder], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_NO_ORDER];
        }

        $dateStart = date('Y-m-d H:i:s', strtotime('-1 year'));

        // 2. Последней заявке больше года
//        if (empty($lastOrder->date) || $lastOrder->date < $dateStart) {
//            $this->logging(__METHOD__, '', 'Последняя заявка старая', ['last_order' => $lastOrder], self::LOG_FILE);
//            return ['success' => false, 'reason' => AutoApproveOrders::REASON_OLD_LAST_ORDER];
//        }

        // 3. Заявка не закрыта или не в отказе (тех отказе) И неподходящая причина отказа И нет промокода на обязательную выдачу ИЛИ это открытый кросс-ордер
        if (!$this->checkOrderStatus($lastOrder)) {
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_LAST_ORDER_NOT_CLOSED];
        }

        return ['success' => true];
    }

    private function checkOrderStatus(stdClass $lastOrder): bool
    {
        // Если кросс-ордер открыт, то разрешаем создавать автозаявку, иначе проверяем статус и причину отказа кросс-ордера
        if ($this->orders->isCrossOrder($lastOrder) && !$this->orders->isOrderClosed($lastOrder)){
            return true;
        }

        // Если займ закрыт, то разрешаем создавать автозаявку
        if (
            $lastOrder->{'1c_status'} === $this->orders::ORDER_1C_STATUS_CLOSED &&
            (int)$lastOrder->status === $this->orders::ORDER_STATUS_CRM_ISSUED
        ) {
            return true;
        }

        $allowedOrderReject1CStatuses = [
            $this->orders::ORDER_1C_STATUS_REJECTED_2,
            $this->orders::ORDER_1C_STATUS_REJECTED_TECH
        ];

        $allowedRejectReasonsId = [
            $this->reasons::REASON_AUTO_APPROVE,
            $this->reasons::REASON_END_TIME,
            $this->reasons::REASON_AUTO_APPROVE_REASON_ID,
            $this->reasons::REASON_UNKNOWN_AXI,
        ];

        // Если отказ по заявке из-за определенной причины или если причина не указана (актуально для кросс-ордеров), то разрешаем создавать автозаявку
        if (
            in_array($lastOrder->{'1c_status'}, $allowedOrderReject1CStatuses) &&
            (empty($lastOrder->reason_id) ||
            in_array($lastOrder->reason_id, $allowedRejectReasonsId))
        ) {
            return true;
        }

        if (empty($lastOrder->promocode)) {
            $this->logging(__METHOD__, '', 'Нет промокода с обязательной выдачей', ['last_order' => $lastOrder], self::LOG_FILE);
            return false;
        }

        $promo = $this->promocodes->getOne([
            'id' => $lastOrder->promocode,
            'is_mandatory_issue' => 1,
        ]);

        // Если есть промокод с обязательной выдачей, то разрешаем создавать автозаявку
        if (!empty($promo)) {
            $this->logging(__METHOD__, '', 'Найден промокод с обязательной выдачей', ['last_order' => $lastOrder, 'promo' => $promo], self::LOG_FILE);
            return true;
        }

        $this->logging(__METHOD__, '', 'Промокод без обязательной выдачи', ['last_order' => $lastOrder], self::LOG_FILE);
        return false;
    }

    /** 3. Проверка карты */
    private function validateCard(stdClass $lastOrder): array
    {
        if (empty($lastOrder->card_id) || $lastOrder->card_type === $this->orders::CARD_TYPE_CARD) {
            return $this->returnNoCardAndNoSbp($lastOrder);
        }

        if ($lastOrder->card_type === $this->orders::CARD_TYPE_SBP) {
            $sbpAccount = $this->sbpAccount->first([
                ['id', '=', (int)$lastOrder->card_id],
                ['deleted', '=', 0],
            ]);

            if (!empty($sbpAccount->id)) {
                return ['success' => true, 'card_id' => (int)$sbpAccount->id, 'card_type' => $this->orders::CARD_TYPE_SBP];
            }

            $this->logging(__METHOD__, '', 'Нет привязанного СБП', ['last_order_id' => (int)$lastOrder->id], self::LOG_FILE);
            return $this->returnNoCardAndNoSbp($lastOrder);
        }

        $this->logging(__METHOD__, '', 'Некорректный card_type', ['last_order_id' => (int)$lastOrder->id], self::LOG_FILE);
        return $this->returnNoCardAndNoSbp($lastOrder);
    }

    /** У клиента нет доступной карты и СБП. Возвращаем card_id = 0, card_type = $this->orders::CARD_TYPE_SBP
     * Сбп или карту потребует уже при выдаче
     */
    private function returnNoCardAndNoSbp(stdClass $lastOrder): array
    {
        $this->logging(__METHOD__, '', 'Нет карты', ['last_order_id' => (int)$lastOrder->id], self::LOG_FILE);
        return ['success' => true, 'card_id' => 0, 'card_type' => $this->orders::CARD_TYPE_SBP];
    }

    /** 4. Добавление и проверка скорингов */
    private function validateScorings(stdClass $task): array
    {
        $result = $this->validateScoring((int)$task->user_id, $this->scorings::TYPE_BLACKLIST);

        if (!$result) {
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_BLACKLIST_SCORING];
        }

        return ['success' => true];
    }

    private function validateScoring(int $userId, int $scoringType): bool
    {
        $scoringId = $this->scorings->add_scoring([
            'order_id' => 0,
            'user_id' => $userId,
            'type' => $scoringType,
            'status' => $this->scorings::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
        ]);

        $scoringTypeName = $this->scorings->get_type($scoringType)->name;
        $this->$scoringTypeName->run_scoring($scoringId);

        $scoring = $this->scorings->get_scoring($scoringId);

        if (empty($scoring->success)) {
            $this->logging(__METHOD__, '', 'Скоринг не пройден', ['scoring' => $scoring], self::LOG_FILE);
            return false;
        }

        return true;
    }

    /** Обновляет запись в кроне */
    public function updateAutoApproveNK(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_auto_approve_nk SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);

        if (isset($data['status']) && !in_array($data['status'], [
                $this->orders_auto_approve::STATUS_CRON_NEW,
                $this->orders_auto_approve::STATUS_CRON_PROCESS
            ])) {
            $item = $this->getAutoApproveNKById($id);
            if ($item && is_object($item)) {
                $user_id = $item->user_id;

                try {
                    $this->centrifugo->publishToChannel("check_auto_approve.$user_id", ['result' => true]);
                } catch (Throwable $error) {
                    $this->logging(__METHOD__, '', 'Ошибка при отправке данных в канал', ['auto_approve_nk_id' => $id, 'data' => $data, 'error' => $error], self::LOG_FILE);
                }
            }
        }

        return $this->db->insert_id();
    }

    /** Берет задание крона */
    public function getAutoApproveNKById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_auto_approve_nk WHERE id = ?", $id);
        $this->db->query($query);

        return $this->db->result();
    }
}

$autoApproveNK = new AutoApproveNK();
$autoApproveNK->run();