<?php

require_once 'lib/autoloader.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';
require_once 'View.php';


class RefundExtraServicesReportView extends View
{
    private const SERVICES_CONFIG = [
        'multipolis' => [
            'table' => 's_multipolis',
            'title' => 'Консьерж сервис',
            'alias' => 'sm',
            'alias_req' => 'smr',
            'join_field' => 'payment_id',
            'types' => ['REFUND_MULTIPOLIS', 'RECOMPENSE_MULTIPOLIS', 'REFUND_MULTIPOLIS_REQUISITES'],
        ],
        'tv_medical' => [
            'table' => 's_tv_medical_payments',
            'title' => 'Вита-мед',
            'alias' => 'st',
            'alias_req' => 'str',
            'join_field' => 'payment_id',
            'types' => ['REFUND_TV_MEDICAL', 'RECOMPENSE_TV_MEDICAL', 'REFUND_TV_MEDICAL_REQUISITES'],
        ],
        'credit_doctor' => [
            'table' => 's_credit_doctor_to_user',
            'title' => 'Кредитный доктор',
            'alias' => 'sc',
            'alias_req' => 'scr',
            'join_field' => 'transaction_id',
            'types' => ['REFUND_CREDIT_DOCTOR', 'RECOMPENSE_CREDIT_DOCTOR', 'REFUND_CREDIT_DOCTOR_REQUISITES'],
        ],
        'star_oracle' => [
            'table' => 's_star_oracle',
            'title' => 'Звездный Оракул',
            'alias' => 'so',
            'alias_req' => 'sor',
            'join_field' => 'transaction_id',
            'types' => ['REFUND_STAR_ORACLE', 'RECOMPENSE_STAR_ORACLE', 'REFUND_STAR_ORACLE_REQUISITES'],
        ],
    ];

    private const CLIENT_TYPE = [
        "0" => 'НК',
        "1" => 'ПК',
    ];

    private const LOAN_SOURCE = [
        'app_android' => 'Мобильное приложение (Android)',
        'app_ios' => 'Мобильное приложение (iOS)',
    ];

    private const REFUND_PERCENTAGES = [100, 75, 50, 25];

    public const PAGE_CAPACITY = 20;
    public const MIN_REPORT_DATE = '2023-09-01';
    private const TOTAL_CACHE_TTL = 300;

    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;

    public function __construct()
    {
        parent::__construct();

        $action = $this->request->get('action');

        if ($action === 'download') {
            $this->totalItems = 0;
            $this->pagesNum = 0;
            $this->currentPage = 1;
        } else {
            $this->totalItems = $this->getTotals();
            $this->pagesNum = max(1, (int)ceil($this->totalItems / self::PAGE_CAPACITY));
            $pageRequest = (int)$this->request->get('page', 'integer');
            $this->currentPage = min($this->pagesNum, max(1, $pageRequest > 0 ? $pageRequest : 1));
        }

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    public function fetch(): string
    {
        [$date_from, $date_to] = $this->getDateRange();

        if (strtotime($date_to) < strtotime(self::MIN_REPORT_DATE . ' 00:00:00') ||
            strtotime($date_from) < strtotime(self::MIN_REPORT_DATE . ' 00:00:00')) {
            $this->design->assign('message_error', 'Отчёт по возвратам формируется с ' . self::MIN_REPORT_DATE);
        }

        $items = $this->getResults($this->currentPage, $date_from, $date_to);

        $this->design->assign('items', $items);
        $this->design->assign('current_page_num', $this->currentPage);
        $this->design->assign('total_pages_num', $this->pagesNum);
        $this->design->assign('total_items', $this->totalItems);
        $this->design->assign('reportUri', strtok($_SERVER['REQUEST_URI'], '?'));
        $this->design->assign('date_from', $date_from);
        $this->design->assign('date_to', $date_to);
        $this->design->assign('from', date('d.m.Y', strtotime($date_from)));
        $this->design->assign('to', date('d.m.Y', strtotime($date_to)));

        return $this->design->fetch('refund_report.tpl');
    }

    /**
     * Получение интервала даты из $_GET
     */
    private function getDateRange(): array
    {
        $daterange = (string)($this->request->get('daterange') ?? '');

        if ($daterange === '') {
            $date_from = date('Y-m-d', strtotime('-1 month'));
            $date_to = date('Y-m-d');
        } else {
            if (strpos($daterange, ' - ') !== false) {
                $parts = explode(' - ', $daterange, 2);
            } else {
                $parts = explode('-', $daterange, 2);
            }
            $from = isset($parts[0]) ? trim($parts[0]) : '';
            $to = isset($parts[1]) ? trim($parts[1]) : '';
            $date_from = $from ? date('Y-m-d', strtotime($from)) : date('Y-m-d', strtotime('-1 month'));
            $date_to = $to ? date('Y-m-d', strtotime($to)) : date('Y-m-d');
        }

        return [
            $date_from . " 0:00:00",
            $date_to . " 23:59:59"
        ];
    }

    /**
     * Подзапрос UNION по таблицам доп. услуг (фильтр по return_date, без b2p_transactions)
     * Возвращает: order_id, user_id, return_date, service_date, amount, return_amount, return_by_user,
     * return_by_manager_id, return_transaction_id, table_name
     */
    private function buildRefundsUnionSubquery(): string
    {
        $statusSuccess = "'" . $this->db->escape($this->multipolis::STATUS_SUCCESS) . "'";

        return "
            (SELECT sm.order_id, sm.user_id, sm.return_date, sm.date_added AS service_date,
                    sm.amount, sm.return_amount, sm.return_by_user, sm.return_by_manager_id,
                    sm.return_transaction_id, 's_multipolis' AS table_name
             FROM s_multipolis sm
             WHERE sm.status = {$statusSuccess} AND sm.return_date IS NOT NULL
               AND sm.return_date >= ? AND sm.return_date <= ?)
            UNION ALL
            (SELECT st.order_id, st.user_id, st.return_date, st.date_added AS service_date,
                    st.amount, st.return_amount, st.return_by_user, st.return_by_manager_id,
                    st.return_transaction_id, 's_tv_medical_payments' AS table_name
             FROM s_tv_medical_payments st
             WHERE st.status = {$statusSuccess} AND st.return_date IS NOT NULL
               AND st.return_date >= ? AND st.return_date <= ?)
            UNION ALL
            (SELECT sc.order_id, sc.user_id, sc.return_date, sc.date_added AS service_date,
                    sc.amount, sc.return_amount, sc.return_by_user, sc.return_by_manager_id,
                    sc.return_transaction_id, 's_credit_doctor_to_user' AS table_name
             FROM s_credit_doctor_to_user sc
             WHERE sc.status = {$statusSuccess} AND sc.return_date IS NOT NULL
               AND sc.return_date >= ? AND sc.return_date <= ?)
            UNION ALL
            (SELECT so.order_id, so.user_id, so.return_date, so.date_added AS service_date,
                    so.amount, so.return_amount, so.return_by_user, so.return_by_manager_id,
                    so.return_transaction_id, 's_star_oracle' AS table_name
             FROM s_star_oracle so
             WHERE so.status = {$statusSuccess} AND so.return_date IS NOT NULL
               AND so.return_date >= ? AND so.return_date <= ?)
        ";
    }

    /**
     * Параметры дат для подзапроса (4 пары: from, to для каждой из 4 таблиц)
     */
    private function getUnionDateParams(string $from_date, string $to_date): array
    {
        return [$from_date, $to_date, $from_date, $to_date, $from_date, $to_date, $from_date, $to_date];
    }

    /**
     * SQL выборки данных отчёта
     */
    private function buildReportDataSql(): string
    {
        return "SELECT refunds.order_id, refunds.user_id, refunds.return_date, refunds.service_date,
                    refunds.amount, refunds.return_amount, refunds.return_by_user, refunds.return_by_manager_id,
                    refunds.return_transaction_id, refunds.table_name,
                    CONCAT(us.lastname, ' ', us.firstname, ' ', us.patronymic) AS fio,
                    us.birth,
                    c.number AS loan_number,
                    o.utm_term AS loan_source,
                    o.have_close_credits,
                    bt.type AS transaction_type,
                    bt.card_pan AS card_pan
             FROM (" . $this->buildRefundsUnionSubquery() . ") AS refunds
             LEFT JOIN s_orders o ON o.id = refunds.order_id
             LEFT JOIN s_users us ON us.id = refunds.user_id
             LEFT JOIN s_contracts c ON c.order_id = refunds.order_id
             LEFT JOIN b2p_transactions bt ON bt.id = refunds.return_transaction_id AND refunds.return_transaction_id > 0
             ORDER BY refunds.return_date DESC";
    }

    /**
     * Выполнение выборки отчёта (с опциональным LIMIT) и форматирование результатов
     *
     * @param array $params Параметры для плейсхолдеров (даты UNION; при LIMIT ?, ? — ещё offset, limit)
     * @param string $limitClause Фрагмент LIMIT или пустая строка
     */
    private function runReportQuery(array $params, string $limitClause): array
    {
        $sql = $this->buildReportDataSql() . ($limitClause !== '' ? ' ' . $limitClause : '');
        $query = $this->db->placehold($sql, ...$params);
        $this->db->query($query);
        $results = $this->db->results() ?: [];
        $results = $this->attachRepeatReturnFlags($results);
        $managersMap = $this->loadManagersMap($this->collectManagerIdsFromResults($results));
        return $this->formatResults($results, $managersMap);
    }

    /**
     * Генерация данных отчета
     */
    private function getResults(int $current_page, string $from_date, string $to_date, bool $get_total_count = false): array
    {
        if ($get_total_count) {
            $params = $this->getUnionDateParams($from_date, $to_date);
            $query = $this->db->placehold(
                "SELECT COUNT(*) AS total FROM (" . $this->buildRefundsUnionSubquery() . ") AS refunds",
                ...$params
            );
            $this->db->query($query);
            $row = $this->db->result();
            return $row ? [(object)['total' => (int)$row->total]] : [(object)['total' => 0]];
        }

        $pageForLimit = $current_page > 0 ? $current_page : 1;
        $limitClause = trim($this->buildLimitClause($pageForLimit));
        $params = $this->getUnionDateParams($from_date, $to_date);

        return $this->runReportQuery($params, $limitClause !== '' ? $limitClause : '');
    }

    /**
     * Получение данных отчета порциями для выгрузки
     */
    private function getResultsBatch(int $offset, int $limit, string $from_date, string $to_date): array
    {
        $params = array_merge($this->getUnionDateParams($from_date, $to_date), [$offset, $limit]);
        return $this->runReportQuery($params, 'LIMIT ?, ?');
    }

    /**
     * Добавляет поле is_repeat_return по счётчику в b2p_transactions только для (reference, type) из выборки
     */
    private function attachRepeatReturnFlags(array $rows): array
    {
        $txIds = [];
        foreach ($rows as $row) {
            if (!empty($row->return_transaction_id)) {
                $txIds[(int)$row->return_transaction_id] = true;
            }
        }
        $txIds = array_keys($txIds);
        if (empty($txIds)) {
            foreach ($rows as $row) {
                $row->is_repeat_return = 'Нет';
            }
            unset($row);
            return $rows;
        }

        $placeholders = implode(',', array_fill(0, count($txIds), '?'));
        $this->db->query($this->db->placehold(
            "SELECT reference, type FROM b2p_transactions WHERE id IN ({$placeholders}) AND reference IS NOT NULL AND type IS NOT NULL",
            ...$txIds
        ));
        $pairs = $this->db->results() ?: [];
        $pairKeys = [];
        foreach ($pairs as $p) {
            $key = $p->reference . "\0" . $p->type;
            $pairKeys[$key] = true;
        }
        if (empty($pairKeys)) {
            foreach ($rows as &$row) {
                $row->is_repeat_return = 'Нет';
            }
            unset($row);
            return $rows;
        }

        $seen = [];
        $pairParams = [];
        foreach ($pairs as $p) {
            $key = $p->reference . "\0" . $p->type;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $pairParams[] = $p->reference;
            $pairParams[] = $p->type;
        }
        $pairListStr = implode(',', array_fill(0, count($pairParams) / 2, '(?,?)'));
        $this->db->query($this->db->placehold(
            "SELECT reference, type, COUNT(*) AS return_count
             FROM b2p_transactions
             WHERE reason_code = 1 AND type IS NOT NULL AND (reference, type) IN ({$pairListStr})
             GROUP BY reference, type",
            ...$pairParams
        ));
        $counts = $this->db->results() ?: [];
        $countMap = [];
        foreach ($counts as $c) {
            $key = $c->reference . "\0" . $c->type;
            $countMap[$key] = (int)$c->return_count;
        }

        $this->db->query($this->db->placehold(
            "SELECT id, reference, type FROM b2p_transactions WHERE id IN ({$placeholders})",
            ...$txIds
        ));
        $idToPair = [];
        foreach (($this->db->results() ?: []) as $r) {
            $idToPair[(int)$r->id] = $r->reference . "\0" . $r->type;
        }

        foreach ($rows as $row) {
            $id = ($row->return_transaction_id ?? 0);
            $pairKey = $idToPair[$id] ?? '';
            $row->is_repeat_return = isset($countMap[$pairKey]) && $countMap[$pairKey] > 1 ? 'Да' : 'Нет';
        }
        unset($row);
        return $rows;
    }

    /**
     * Построение LIMIT части запроса
     */
    private function buildLimitClause(int $current_page): string
    {
        if (!$current_page) {
            return "";
        }

        $offset = self::PAGE_CAPACITY * ($current_page - 1);
        $amount = self::PAGE_CAPACITY;

        return $this->db->placehold("LIMIT ?,?", $offset, $amount);
    }

    /**
     * Сбор уникальных id менеджеров из результатов
     */
    private function collectManagerIdsFromResults(array $services): array
    {
        $ids = [];
        foreach ($services as $service) {
            if (empty($service->return_by_user) && !empty($service->return_by_manager_id)) {
                $ids[(int)$service->return_by_manager_id] = true;
            }
        }
        return array_keys($ids);
    }

    /**
     * Загрузка менеджеров id => name одним запросом
     */
    private function loadManagersMap(array $managerIds): array
    {
        if (empty($managerIds)) {
            return [];
        }
        $managers = $this->managers->get_managers(['id' => array_values($managerIds)]);
        $map = [];
        foreach ($managers ?: [] as $m) {
            $map[(int)$m->id] = $m->name ?? 'Неизвестно';
        }
        return $map;
    }

    /**
     * Форматирование результатов запроса
     */
    private function formatResults(array $services, array $managersMap = []): array
    {
        foreach ($services as &$service) {
            $service = (object)[
                'order_id' => $service->order_id,
                'fio' => $service->fio,
                'birth' => $service->birth,
                'loan_number' => $service->loan_number,
                'loan_source' => $this->formatLoanSource($service->loan_source),
                'service_date' => $service->service_date,
                'return_date' => $service->return_date,
                'refund_amount' => number_format($service->return_amount, 2, '.', ''),
                'refund_percent' => $this->calculateRefundPercent((float)$service->return_amount, $service->amount !== null ? (float)$service->amount : null),
                'repeat_refund' => $service->is_repeat_return,
                'card_number' => $this->formatPaymentMethod($service->transaction_type, $service->card_pan),
                'service_title' => $this->getServiceTitle($service->table_name),
                'returned_by' => $this->formatReturnedBy($service, $managersMap),
                'client_type' => self::CLIENT_TYPE[$service->have_close_credits] ?? 'Неизвестно',
            ];
        }

        return $services;
    }

    /**
     * Форматирование источника займа
     */
    private function formatLoanSource(?string $loan_source): string
    {
        if (empty($loan_source)) {
            return 'Сайт';
        }

        return self::LOAN_SOURCE[$loan_source] ?? 'Сайт';
    }

    /**
     * Форматирование способа возврата (карта/реквизиты/СБП/взаимозачёт)
     */
    private function formatPaymentMethod(?string $transaction_type, ?string $card_pan): string
    {
        if ($transaction_type === null || $transaction_type === '') {
            return 'Не указан';
        }
        if (str_starts_with($transaction_type, 'RECOMPENSE_')) {
            return 'Взаимозачёт';
        }

        if (str_ends_with($transaction_type, '_REQUISITES')) {
            return 'По реквизитам';
        }

        if (str_starts_with($transaction_type, 'REFUND_')) {
            if (!empty($card_pan)) {
                return "Карта {$card_pan}";
            }

            return 'СБП';
        }

        return 'Не указан';
    }

    /**
     * Вычисление процента возврата
     */
    private function calculateRefundPercent(float $return_amount, ?float $amount): string
    {
        $amount = (float)$amount;
        if ($amount <= 0) {
            return 'Неизвестно';
        }

        $epsilon = 1.0;
        foreach (self::REFUND_PERCENTAGES as $percent) {
            if (abs($return_amount - ($amount * $percent / 100)) < $epsilon) {
                return "{$percent}%";
            }
        }

        return 'Неизвестно';
    }

    /**
     * Получение названия сервиса
     */
    private function getServiceTitle(?string $table_name): string
    {
        foreach (self::SERVICES_CONFIG as $config) {
            if ($config['table'] === $table_name) {
                return $config['title'];
            }
        }

        return 'Неизвестно';
    }

    /**
     * Форматирование "Кто вернул"
     */
    private function formatReturnedBy(object $service, array $managersMap = []): string
    {
        if ($service->return_by_user) {
            return 'Самостоятельно';
        }

        $managerId = (int)($service->return_by_manager_id ?? 0);
        return $managersMap[$managerId] ?? 'Неизвестно';
    }

    /**
     * Получение итогового количества записей
     */
    private function getTotals(): int
    {
        [$date_from, $date_to] = $this->getDateRange();

        $cacheKey = "refund_report_total_{$date_from}_{$date_to}";

        if (isset($_SESSION[$cacheKey], $_SESSION[$cacheKey . '_time']) &&
            (time() - $_SESSION[$cacheKey . '_time']) < self::TOTAL_CACHE_TTL) {
            return (int)$_SESSION[$cacheKey];
        }

        $result = $this->getResults(0, $date_from, $date_to, true);
        $total = isset($result[0]) && isset($result[0]->total) ? (int)$result[0]->total : 0;

        $_SESSION[$cacheKey] = $total;
        $_SESSION[$cacheKey . '_time'] = time();

        return $total;
    }

    /**
     * Выгрузка данных в Excel
     */
    private function download(): void
    {
        ignore_user_abort(false);
        ini_set('memory_limit', '768M');
        ini_set('max_execution_time', '600');

        [$date_from, $date_to] = $this->getDateRange();

        $header = [
            'Клиент' => 'string',
            'Номер договора' => 'string',
            'Источник займа' => 'string',
            'Дата услуги' => 'string',
            'Дата возврата' => 'string',
            'Кто вернул' => 'string',
            'Вид услуги' => 'string',
            'Банковская карта' => 'string',
            'Сумма' => 'string',
            'Процент' => 'string',
            'НК/ПК' => 'string',
            'Повторный возврат' => 'string',
        ];

        $writer = new XLSXWriter();
        $colWidths = [30, 18, 15, 12, 12, 18, 25, 22, 12, 10, 10, 18];
        $writer->writeSheetHeader('additional_orders_report', $header, ['widths' => $colWidths]);

        $batchSize = 500;
        $offset = 0;

        while (true) {
            if (connection_aborted()) {
                exit;
            }

            $items = $this->getResultsBatch($offset, $batchSize, $date_from, $date_to);

            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                $row_data = [
                    $item->fio . ' ' . $item->birth,
                    $item->loan_number,
                    $item->loan_source,
                    $item->service_date ? date('d.m.Y', strtotime($item->service_date)) : '',
                    $item->return_date ? date('d.m.Y', strtotime($item->return_date)) : '',
                    $item->returned_by,
                    $item->service_title,
                    $item->card_number,
                    $item->refund_amount,
                    $item->refund_percent,
                    $item->client_type,
                    $item->repeat_refund,
                ];

                $writer->writeSheetRow('additional_orders_report', $row_data);
            }

            $offset += $batchSize;

            if (count($items) < $batchSize) {
                break;
            }
        }

        $filename = 'files/reports/report__extra_service_refund__' . date('d.m.Y') . '.xlsx';
        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location: /' . $filename);
        exit;
    }
}
