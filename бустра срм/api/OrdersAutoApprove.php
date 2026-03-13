<?php

use App\Enums\AutoApproveOrders;

ini_set('max_execution_time', '1200');
ini_set('default_socket_timeout', 5);

require_once(__DIR__ . '/../api/Simpla.php');

/**
 * API для работы с автоодобрениями
 */
class OrdersAutoApprove extends Simpla
{
    private const LOG_FILE = 'auto_approve_nk.txt';

    private const MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C = 3;

    /** @var int Максимальная продолжительность генерации автозаявки (в секундах) */
    public const MAX_DURATION_TO_GENERATE_AUTO_APPROVE_ORDER = 7200; // 2 часа

    /** @var int Максимальная продолжительность ожидания результата акси (в секундах) */
    public const MAX_AXI_DURATION = 3600; // 1 час

    /** Максимальная кол-во попыток выполнить скоринг акси (для случая, если предыдущий завершился с ошибкой) */
    public const MAX_AXILINK_SCORINGS_AMOUNT = 3;

    /** Максимальная кол-во попыток выполнить скоринг проверки отчетов */
    public const MAX_REPORT_SCORINGS_AMOUNT = 5;

    /** Статус одобренной автозаявки */
    public const STATUS_SUCCESS = 'SUCCESS';

    /** Статус в работе */
    public const STATUS_PROGRESS = 'PROGRESS';

    /** Статус нового автозаявки */
    public const STATUS_NEW = 'NEW';

    /** Статус отказанной автозаявки */
    public const STATUS_ERROR = 'ERROR';

    /** Сумма для создания автоодобрения */
    public const ORDER_AMOUNT = 30000;

    /** Период для автоодобрения */
    public const ORDER_PERIOD = 16;

    /** Максимальное кол-во закрытых займов за год */
    public const MAX_CLOSED_ORDERS_AMOUNT_PER_YEAR = 8;

    /** Статус нового задания в крон */
    public const STATUS_CRON_NEW = 'NEW';

    /** Статус когда крон взят в работу */
    public const STATUS_CRON_PROCESS = 'PROCESS';

    /** Статус если время ожидания обработки крона истекло */
    public const STATUS_CRON_ERROR_TIMEOUT = 'ERROR_TIMEOUT';

    /** Статус если пользователь не прошел проверку по условиям БД */
    public const STATUS_CRON_ERROR_VALIDATE = 'ERROR_VALIDATE';

    /** Статус если не прошел проверку по скорингу */
    public const STATUS_CRON_ERROR_SCORING = 'ERROR_SCORING';

    /** Статус если карта была удалена */
    public const STATUS_CRON_ERROR_CREDIT_CARD = 'ERROR_CREDIT_CARD';

    /** Статус если не удалось создать автоодобрение */
    public const STATUS_CRON_ERROR_GENERATE = 'ERROR_GENERATE';

    /** Статус если создалось автоодобрение */
    public const STATUS_CRON_SUCCESS = 'SUCCESS';

    /**
     * Генерирует автоодобрения
     *
     * @param stdClass $lastOrder
     * @param stdClass $user
     * @param int $cardId
     * @param string $cardType
     * @return array
     */
    public function createAutoApproveOrder(stdClass $lastOrder, stdClass $user, int $cardId, string $cardType): array
    {
        $baseOrganizationId = $this->organizations->get_base_organization_id(['user_id' => (int)$user->id, 'check_last_report_date' => 1, 'order_id' => (int)$lastOrder->id]);

        $soap_zayavka_params = [
            'utm_source' => $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE,
            'utm_medium' => 'CRM',
            'organization_id' => $baseOrganizationId,
            'order_uid' => exec($this->config->root_dir . 'generic/uidgen')
        ];

        // 1. Сохранить автозаявку в 1С
        $soap_zayavka = $this->addAutoApproveOrderTo1C($lastOrder, $cardId, $soap_zayavka_params);

        if (empty($soap_zayavka->return->id_zayavka) || mb_strpos($soap_zayavka->return->id_zayavka, 'Не принято') !== false) {
            $this->logging(__METHOD__, '', 'Не удалось создать заявку в 1С', ['last_order_id' => (int)$lastOrder->id, 'soap_zayavka' => $soap_zayavka], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_ORDER_NOT_ADDED_TO_1C];
        }

        $credits_history = $this->soap->get_user_credits($user->UID, $user->site_id);
        $user->loan_history = $this->users->save_loan_history($user->id, $credits_history);

        $addOrderToDbParams = [
            '1c_id' => $soap_zayavka->return->id_zayavka,
            'card_id' => $cardId,
            'card_type' => $cardType,
            'organization_id' => $soap_zayavka_params['organization_id'],
            'order_uid' => $soap_zayavka_params['order_uid'],
        ];

        // 2. Создать автозаявку в БД
        $newOrderId = $this->addAutoApproveOrderToDb($user, $addOrderToDbParams);

        if (empty($newOrderId)) {
            $this->logging(__METHOD__, '', 'Не удалось сохранить заявку в БД', ['last_order_id' => (int)$lastOrder->id, 'new_order_id' => $newOrderId], self::LOG_FILE);
            return ['success' => false, 'reason' => AutoApproveOrders::REASON_ORDER_NOT_ADDED_TO_DB];
        }

        $this->logging(__METHOD__, '', 'Успешно создана автозаявка', ['last_order_id' => (int)$lastOrder->id, 'new_order_id' => $newOrderId], self::LOG_FILE);

        // 3. Сохранить запись об автоодобрении
        $this->saveRecordToAutoApproveTable((int)$user->id, $newOrderId);

        // 4. Установить флаг complete в 1С
        try {
            $this->soap->set_order_complete($newOrderId);
        } catch (Throwable $error) {
            $this->logging(__METHOD__, '', 'Не удалось установить флаг complete в 1С', ['last_order_id' => (int)$lastOrder->id, 'error' => $error], self::LOG_FILE);
        }

        // Добавляем УПРИД
        $this->scorings->add_scoring([
            'user_id' => (int)$user->id,
            'order_id' => $newOrderId,
            'type' => $this->scorings::TYPE_UPRID,
        ]);

        // 6. Добавляем акси
        $this->addAxilinkScoring($newOrderId, (int)$user->id);

        return ['success' => true, 'reason' => $newOrderId];
    }

    private function addAxilinkScoring(int $orderId, int $userId): void
    {
        $this->scorings->add_scoring([
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => $this->scorings::TYPE_AXILINK,
            'status' => $this->scorings::STATUS_NEW,
            'start_date' => date('Y-m-d H:i:s')
        ]);
    }

    private function addAutoApproveOrderTo1C(stdClass $lastOrder, int $cardId, array $soap_zayavka_params)
    {
        $soap_zayavka = new stdClass();

        // Пробуем несколько раз создать заявку с задержкой на случай, если 1C недоступен
        for ($i = 0; $i < self::MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C; $i++) {
            $soap_zayavka = $this->soap->soap_repeat_zayavka($lastOrder->amount, $lastOrder->period, $lastOrder->user_id, $cardId, null, $soap_zayavka_params);

            if (!empty($soap_zayavka->return->id_zayavka) && mb_strpos($soap_zayavka->return->id_zayavka, 'Не принято') === false) {
                return $soap_zayavka;
            }

            sleep(5);
        }

        return $soap_zayavka;
    }

    private function addAutoApproveOrderToDb(stdClass $user, array $addOrderToDbParams): ?int
    {
        // создаем в базе order
        $date_create = date('Y-m-d H:i:s');
        $order = array(
            'card_id' => $addOrderToDbParams['card_id'],
            'card_type' => $addOrderToDbParams['card_type'],
            'amount' => $this->settings->sum_order_auto_approve['default'] ?? self::ORDER_AMOUNT,
            'period' => self::ORDER_PERIOD,
            'user_id' => $user->id,
            'ip' => '127.0.0.1',
            'first_loan' => 0,
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'date' => $date_create,
            'accept_date' => $date_create,
            'local_time' => $date_create,
            'utm_source' => $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE,
            'utm_medium' => 'CRM',
            'utm_campaign' => 'C1_main',
            'utm_content' => '',
            'utm_term' => '',
            'webmaster_id' => '',
            'click_hash' => '',
            'percent' => $this->orders::BASE_PERCENTS,
            'status' => 1,
            '1c_id' => $addOrderToDbParams['1c_id'],
            'have_close_credits' => 1,
            'b2p' => 1,
            'organization_id' => $addOrderToDbParams['organization_id'],
            'order_uid' => $addOrderToDbParams['order_uid'],
        );

        $order_id = $this->orders->add_order($order);

        if (!empty($order_id)) {
            return (int)$order_id;
        }

        return null;
    }

    public function saveRecordToAutoApproveTable(int $userId, int $newOrderId)
    {
        $orders = $this->orders->get_orders([
            'user_id' => $userId,
        ]);

        // TODO нужны ли закрытые займы только в текущем МКК или вообще все?
        $closedOrders = [];
        foreach ($orders as $order) {
            if ($order->status_1c === $this->orders::ORDER_1C_STATUS_CLOSED) {
                $closedOrders[] = $order;
            }
        }

        $autoApproveId = $this->addAutoApproveOrder([
            'user_id' => $userId,
            'order_id' => $newOrderId,
            'pk_type' => count($closedOrders),
            'status' => self::STATUS_NEW,
            'date_end' => date('Y-m-d H:i:s', strtotime('+7 days')), // не менять date_end (выборка по нему в cron/reject_auto_approve_orders.php)
        ]);

        $this->logging(__METHOD__, '', 'Добавлена запись об автозаявке', ['new_order_id' => $newOrderId, 'auto_approve_id' => $autoApproveId], self::LOG_FILE);
    }

    /**
     * @param int $orderId
     * @param int $autoApproveId
     * @return void
     */
    public function handleAxiDecision(int $orderId, int $autoApproveId): void
    {
        $order = $this->orders->get_order($orderId);

        // 1. Отказываем по заявке, если время создания автозаявки истекло
        if ($this->isTimeout((int)strtotime($order->date), self::MAX_AXI_DURATION)) {
            $this->rejectOrderForTimeoutReason($order, $autoApproveId);
            return;
        }

        // 2. Если заявка уже выдана или отказ
        if (in_array($order->status, [$this->orders::ORDER_STATUS_CRM_ISSUED, $this->orders::ORDER_STATUS_CRM_REJECT])) {
            $this->rejectOrderForInappropriateStatusReason($order, $autoApproveId);
            return;
        }

        $axiScorings = $this->scorings->get_scorings([
            'order_id' => $orderId,
            'type' => $this->scorings::TYPE_AXILINK,
            'sort' => 'id_date_desc'
        ]);

        // 3. Отказываем по заявке, если скоринг акси не найден или добавлено больше максимального кол-ва
        if (empty($axiScorings) || count($axiScorings) > self::MAX_AXILINK_SCORINGS_AMOUNT) {
            $this->rejectOrderForNoAxiOrMaximumAxiReason($order, $autoApproveId, $axiScorings);
            return;
        }

        $lastAxiScoring = $axiScorings[0];

        // 4. Если скоринг акси завершился с ошибкой, то добавляем новый
        if ((int)$lastAxiScoring->status === $this->scorings::STATUS_ERROR) {
            $this->waitDecisionForNewAxiScoring($order, $autoApproveId, $lastAxiScoring);
            return;
        }

        // 5. Если скоринг не завершен, то ждем
        if ((int)$lastAxiScoring->status !== $this->scorings::STATUS_COMPLETED) {
            $this->waitDecisionForCurrentAxiScoring($order, $autoApproveId, $lastAxiScoring);
            return;
        }

        // 6. Если почему-то нет решения от акси, то добавляем новый
        if (empty($lastAxiScoring->scorista_status) && empty($lastAxiScoring->string_result)) {
            $this->waitDecisionForNewAxiScoring($order, $autoApproveId, $lastAxiScoring);
            return;
        }

        // 7. Если нужно, проверяем актуальность ССП и КИ отчетов
        if ($this->report->needCheckReports($orderId) && !$this->checkReports($order)) {
            $this->waitDecisionForReportScoring($order, $autoApproveId);
            return;
        }

        $lastAxiScoring->body = $this->scorings->get_body_by_type($lastAxiScoring);

        // 8. Отказываем по заявке, если пустой body
        if (empty($lastAxiScoring->body)) {
            $this->rejectOrderForEmptyAxiScoringBodyReason($order, $autoApproveId, $lastAxiScoring);
            return;
        }

        // 9. Одобряем автозаявку, если скоринг акси пройден успешно, иначе отказываем по заявке
        if (!empty($lastAxiScoring->success)) {

            $orderOrgSwitchResult = $this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_RESULT);
            $orderOrgSwitchParentOrderId = $this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);

            // 10. Если нет результата смены организации или не меняли организацию, то ждем
            if (empty($orderOrgSwitchResult) && empty($orderOrgSwitchParentOrderId)) {
                $this->waitOrderOrgSwitchResult($order, $autoApproveId);
                return;
            }

            // 11. Заново проверяем статус заявки, т.к. статус мог измениться
            $order = $this->orders->get_order((int)$order->order_id);
            if (in_array($order->status, [$this->orders::ORDER_STATUS_CRM_ISSUED, $this->orders::ORDER_STATUS_CRM_REJECT])) {
                $this->logging(__METHOD__, '', 'Неподходящий статус заявки перед одобрением', ['order' => $order], self::LOG_FILE);
                $this->rejectOrderForInappropriateStatusReason($order, $autoApproveId);
                return;
            }

            $this->approveOrder($order, $lastAxiScoring, $autoApproveId);
        } else {
            $axiRejectReason = $this->dbrainAxi->getRejectReason((int)$order->user_id, $lastAxiScoring->body->message ?? '');

            // 12. Отказываем по заявке по причине $axiRejectReason
            if (!empty($axiRejectReason)) {
                $this->rejectOrderForAxiScoringResultReason($order, $autoApproveId, $lastAxiScoring, $axiRejectReason);
                return;
            }

            // 13. Отказываем по заявке по неизвестной причине
            $this->rejectOrderForUnknownReason($order, $autoApproveId, $lastAxiScoring, $axiRejectReason);
        }
    }

    private function waitOrderOrgSwitchResult(stdClass $order, int $autoApproveId)
    {
        $this->logging(__METHOD__, '', 'Результата по смене организации еще нет', ['order' => $order], self::LOG_FILE);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_NEW, 'reason' => AutoApproveOrders::REASON_ORDER_ORG_SWITCH_WAIT]);
    }

    public function isTimeout(int $timestamp, int $timeLimit = self::MAX_DURATION_TO_GENERATE_AUTO_APPROVE_ORDER): bool
    {
        $diffSeconds = time() - $timestamp;
        return $diffSeconds > $timeLimit;
    }

    private function rejectOrderForTimeoutReason(stdClass $order, int $autoApproveId)
    {
        $this->logging(__METHOD__, '', 'Истекло время создания автозаявки', ['order' => $order], self::LOG_FILE);
        $this->orders->rejectOrder($order, $this->reasons::REASON_AUTO_APPROVE_REASON_ID);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_ERROR, 'reason' => AutoApproveOrders::REASON_TIMEOUT]);
    }

    private function rejectOrderForInappropriateStatusReason(stdClass $order, int $autoApproveId)
    {
        $this->logging(__METHOD__, '', 'Неподходящий статус', ['order' => $order], self::LOG_FILE);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_ERROR, 'reason' => AutoApproveOrders::REASON_INAPPROPRIATE_STATUS]);
    }

    private function rejectOrderForNoAxiOrMaximumAxiReason(stdClass $order, int $autoApproveId, $axiScorings)
    {
        $this->logging(__METHOD__, '', 'Скоринг акси не найдено или добавлено максимальное кол-во', ['order' => $order, 'axi_scorings' => $axiScorings], self::LOG_FILE);
        $this->orders->rejectOrder($order, $this->reasons::REASON_AUTO_APPROVE_REASON_ID);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_ERROR, 'reason' => AutoApproveOrders::NO_AXI_OR_MAXIMUM_AXI]);
    }

    private function waitDecisionForNewAxiScoring(stdClass $order, int $autoApproveId, stdClass $lastAxiScoring)
    {
        $this->logging(__METHOD__, '', 'Ошибка скоринга акси, добавлен новый скоринг', ['order' => $order, 'last_axi_scoring' => $lastAxiScoring], self::LOG_FILE);
        $this->addAxilinkScoring((int)$order->order_id, (int)$order->user_id);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_NEW, 'reason' => AutoApproveOrders::REASON_AXI_ERROR]);
    }

    private function waitDecisionForCurrentAxiScoring(stdClass $order, int $autoApproveId, stdClass $lastAxiScoring)
    {
        $this->logging(__METHOD__, '', 'Ожидание завершения скоринга акси', ['order' => $order, 'last_axi_scoring' => $lastAxiScoring], self::LOG_FILE);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_NEW, 'reason' => AutoApproveOrders::REASON_AXI_WAIT]);
    }

    private function waitDecisionForReportScoring(stdClass $order, int $autoApproveId)
    {
        $this->logging(__METHOD__, '', 'Ожидание завершения скоринга проверки отчетов', ['order' => $order], self::LOG_FILE);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_NEW, 'reason' => AutoApproveOrders::REASON_REPORT_WAIT]);
    }

    private function rejectOrderForEmptyAxiScoringBodyReason(stdClass $order, int $autoApproveId, stdClass $lastAxiScoring)
    {
        $this->logging(__METHOD__, '', 'Пустой результат скоринга акси', ['order' => $order, 'last_axi_scoring' => $lastAxiScoring], self::LOG_FILE);
        $this->orders->rejectOrder($order, $this->reasons::REASON_AUTO_APPROVE_REASON_ID);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_ERROR, 'reason' => AutoApproveOrders::REASON_AXI_EMPTY_BODY]);
    }

    private function rejectOrderForAxiScoringResultReason(stdClass $order, int $autoApproveId, stdClass $lastAxiScoring, int $axiRejectReason)
    {
        $this->logging(__METHOD__, '', 'Отказ заявки по причине из акси', ['order' => $order, 'last_axi_scoring' => $lastAxiScoring], self::LOG_FILE);
        $this->orders->rejectOrder($order, $axiRejectReason);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_ERROR, 'reason' => $axiRejectReason]);
    }

    private function rejectOrderForUnknownReason(stdClass $order, int $autoApproveId, stdClass $lastAxiScoring, int $axiRejectReason)
    {
        $this->logging(__METHOD__, '', 'Отказ по неизвестной причине', ['order' => $order, 'last_axi_scoring' => $lastAxiScoring, 'axi_reject_reason' => $axiRejectReason], self::LOG_FILE);
        $this->orders->rejectOrder($order, $this->reasons::REASON_UNKNOWN_AXI);
        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_ERROR, 'reason' => AutoApproveOrders::REASON_AXI_UNKNOWN_REASON]);
    }

    /**
     * Проверяет ССП и КИ отчеты для заявки
     *
     * @param $order
     * @return bool
     */
    private function checkReports($order): bool
    {
        $this->logging(__METHOD__, '', ['order_id' => $order->order_id], 'Проверка ССП и КИ отчетов для заявки', self::LOG_FILE);

        $reportScorings = $this->scorings->get_scorings([
            'order_id' => $order->order_id,
            'type' => $this->scorings::TYPE_REPORT,
            'sort' => 'id_date_desc'
        ]);

        if (!empty($reportScorings) && is_array($reportScorings)) {
            $lastReportScoring = $reportScorings[0];

            // Если последний скоринг был успешным, то заново скоринг не проводим
            if (!empty($lastReportScoring) && !empty($lastReportScoring->success)) {
                return true;
            }

            // Если проведено больше 10 скорингов, то новый скоринг не добавляем
            if (count($reportScorings) > self::MAX_REPORT_SCORINGS_AMOUNT) {
                return false;
            }
        }

        // Добавить скоринг проверки ССП и КИ отчетов
        $reportScoringId = $this->scorings->add_scoring([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'type' => $this->scorings::TYPE_REPORT,
            'status' => $this->scorings::STATUS_NEW,
        ]);

        $reportScoring = $this->scorings->get_scoring($reportScoringId);

        try {
            // Запустить скоринг проверки ССП и КИ отчетов вручную
            $this->report->run_scoring($reportScoring);
        } catch (Throwable $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];
            $this->logging(__METHOD__, '', ['scoring_id' => $reportScoring->id], ['error' => $error], self::LOG_FILE);
        }

        $reportScoring = $this->scorings->get_scoring($reportScoringId);
        return !empty($reportScoring) && !empty($reportScoring->success);
    }

    /**
     * Добавляет автоодобрение
     * @param array $data
     * @return mixed
     */
    public function addAutoApproveOrder(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_orders_auto_approve SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Get no validated auto approve orders
     * @return bool|array
     */
    public function getNoValidatedAutoApproveOrders()
    {
        $sql = "SELECT 
                    order_id,
                    id as auto_approve_id
                FROM 
                    __orders_auto_approve
                WHERE 1 = 1
                AND `status` IN(?@)";
        $this->db->query(
            $this->db->placehold($sql, [
                self::STATUS_PROGRESS,
                self::STATUS_NEW
            ])
        );
        return $this->db->results();
    }

    /**
     * Обновляет автоодобрение
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateAutoApproveOrder(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_orders_auto_approve SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Получаем автоодобрение
     * @param int $id
     * @return false|int
     */
    public function getAutoApproveOrder(int $id)
    {
        $sql = "SELECT * FROM s_orders_auto_approve WHERE id = ?";
        $query = $this->db->placehold($sql, $id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Создает задание в CRON для добавления заявок НК
     * @param $data
     * @return mixed
     */
    public function addAutoApproveNK($data)
    {
        if (empty($data['user_id']))
            return false;

        $query = $this->db->placehold("INSERT INTO s_auto_approve_nk SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Получает актуальные задания для КРОН
     * @param $filter_data
     * @return array|false
     */
    public function getAutoApprovesNK($filter_data)
    {
        $where = [];

        $sql = "SELECT 
                    * 
                FROM s_auto_approve_nk 
                WHERE user_id IS NOT NULL
                -- {{where}}";

        //  Переход с бустры на аквариус, автозаявки на время теста генерим только для новых закрытых займов
        $where[] = $this->db->placehold("date_added > '2024-03-12 00:00:00'");

        if (!empty($filter_data['user_ids'])) {
            $where[] = $this->db->placehold("user_id IN (?@)", $filter_data['user_ids']);
        }

        if (!empty($filter_data['statuses'])) {
            $where[] = $this->db->placehold("status IN (?@)", $filter_data['statuses']);
        }

        if (!empty($filter_data['date_cron_later'])) {
            $where[] = $this->db->placehold("date_cron <= NOW()");
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Отправляет смс с АСП-кодом
     *
     * @param stdClass $order
     * @return void
     */
    public function sendSmsWithAspCode(stdClass $order): void
    {
        $urlCodes = $this->smsShortLink->getShortLink([
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
            'type' => $this->smsShortLink::SHORT_LINK_TYPE_LK
        ]);

        if (!empty($urlCodes)) {
            return;
        }

        $linkCode = $this->smsShortLink->generateLink((int)$order->user_id, $order->phone_mobile, '', $this->smsShortLink::SHORT_LINK_TYPE_LK, $order->order_id);

        $site_id = $this->users->get_site_id_by_user_id($order->user_id);
        $template = $this->sms->get_template($this->sms::LK_TYPE, $site_id);

        if (empty($template) || empty($template->template)) {
            return;
        }

        $aspCode = mt_rand(1000, 9999);
        $domain = $this->sites->getDomainBySiteId($site_id);

        $text = strtr($template->template, [
            '{{code}}' => $aspCode,
            '{{url}}' => $domain . '/pay/' . $linkCode,
        ]);

        $response = $this->smssender->send_sms(
            $order->phone_mobile,
            $text,
            $site_id,
            1
        );

        $this->sms->add_message([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'phone' => $order->phone_mobile,
            'message' => $text,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $response[1],
            'delivery_status' => '',
            'send_id' => $response[0],
            'type' => $this->smssender::TYPE_ASP,
            'code' => $aspCode
        ]);

        $this->order_data->set((int)$order->order_id, $this->order_data::NEED_AUTO_CONFIRM, '1');
    }

    private function approveOrder(stdClass $order, stdClass $lastAxiScoring, int $autoApproveId)
    {
        $approveAmount = $this->calculateApproveAmount($order, $lastAxiScoring);

        $updateStatus1CResult = $this->updateOrderStatusIn1C($order, $approveAmount);

        if ($updateStatus1CResult !== 'OK') {
            $this->logging(__METHOD__, '', 'Не удалось обновить статус заявки в 1С', ['order' => $order, 'update_status_1c_result' => $updateStatus1CResult], self::LOG_FILE);
            $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_NEW, 'reason' => AutoApproveOrders::REASON_NOT_UPDATED_ORDER_1C_STATUS]);
            return;
        }

        $this->orders->update_order((int)$order->order_id, [
            'status' => $this->orders::ORDER_STATUS_CRM_APPROVED,
            'amount' => $approveAmount,
            '1c_status' => $this->orders::ORDER_1C_STATUS_APPROVED,
            'approve_date' => date('Y-m-d H:i:s'),
        ]);

        $this->updateAutoApproveOrder($autoApproveId, ['status' => self::STATUS_SUCCESS, 'reason' => null]);

        $user = $this->users->get_user((int)$order->user_id);

        $this->smssender->sendApprovedSms($user, (int)$order->order_id, $approveAmount);
        $this->finroznica->send_user($user);

        $auto_confirm_for_auto_approve_orders_enable = (bool)$this->settings->auto_confirm_for_auto_approve_orders_enable;
        if (!empty($auto_confirm_for_auto_approve_orders_enable)) {
            $this->sendSmsWithAspCode($order);
        }

        try {
            $this->cross_orders->create((int)$order->order_id);
        } catch (Throwable $error) {
            $this->logging(__METHOD__, '', 'Не удалось создать кросс ордер', ['order' => $order, 'error' => $error], self::LOG_FILE);
        }
    }

    private function updateOrderStatusIn1C(stdClass $order, int $approveAmount): string
    {
        $updateStatus1CResult = '';

        for ($i = 0; $i < self::MAX_ATTEMPTS_TO_SEND_ORDER_TO_1C; $i++) {
            $updateStatus1CResult = $this->soap->update_status_1c(
                $order->id_1c,
                'Одобрено',
                '',
                $approveAmount,
                1,
                '',
                0,
                $order->period
            );

            if ($updateStatus1CResult === 'OK') {
                return $updateStatus1CResult;
            }

            sleep(5);
        }

        return $updateStatus1CResult;
    }

    /**
     * Рассчитывает сумму одобрения займа
     */
    private function calculateApproveAmount(stdClass $order, stdClass $lastAxiScoring): int
    {
        $approve_amount = $this->order_data->read((int)$order->order_id, 'amount_after_axi');

        if (empty($approve_amount)) {
            $type_scoring_amount = $this->settings->sum_order_auto_approve['scoring'];

            $approve_amount = (($lastAxiScoring->body->{$type_scoring_amount} ?? null)
                ?: $lastAxiScoring->body->{$this->axilink::KEY_AMOUNT_WITH_PDN} ?? null)
                ?: $order->amount;
        }

        if ($this->settings->autoapprove_plus_30 && $lastAxiScoring->scorista_ball >= 500) {
            $approve_amount = min(28000, round($approve_amount * 1.3 / 100) * 100);
        }

        return min($approve_amount, self::ORDER_AMOUNT);
    }
}
