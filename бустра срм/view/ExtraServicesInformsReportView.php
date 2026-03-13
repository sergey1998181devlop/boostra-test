<?php

require_once 'View.php';

class ExtraServicesInformsReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private string $dateFrom;
    private string $dateTo;

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();
        $this->totalItems = $this->getTotals();
        $this->pagesNum = (int) ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->handleAction();
    }

    private function handleAction(): void
    {
        $action = $this->request->get('action');
        if ($action && method_exists($this, $action)) {
            $this->$action();
        }
    }

    public function fetch(): string
    {
        $items = $this->getResults($this->currentPage);

        $this->design->assign_array(array(
            'items' => $items,
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'can_see_manager_url' => in_array('verificators', $this->manager->permissions),
            'can_see_client_url' => in_array('clients', $this->manager->permissions),
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
        ));

        return $this->design->fetch('extra_services_informs_report.tpl');
    }

    private function setupDateRange(): void
    {
        $daterange = $this->request->get('daterange');

        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('-1 month')) . ' - ' . date('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);
        $this->dateFrom = date('Y-m-d', strtotime($from));
        $this->dateTo = date('Y-m-d', strtotime($to));
    }

    private function getResults(int $currentPage)
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);
        $services = [];

        try {
            $this->db->query("
            SELECT
                u.lastname, u.firstname, u.patronymic,
                TIMESTAMPDIFF(YEAR, STR_TO_DATE(u.birth, '%d.%m.%Y'), CURDATE()) AS age,
                CASE
                    WHEN o.first_loan = o.have_close_credits THEN 'N/A'
                    WHEN o.first_loan THEN 'НК'
                    WHEN o.have_close_credits THEN 'ПК'
                END AS client_type,
                esi.user_id, esi.order_id,
                COALESCE(esi.contract, c.number) AS contract,
                TIMESTAMPDIFF(DAY, c.create_date, CURDATE()) AS days_passed,
                m.name AS manager_name, m.id AS manager_id,
                esi.service_name, esi.sms_template_id,
                DATE(esi.created_at) AS sent_at_date,
                TIME(esi.created_at) AS sent_at_time,
                esi.sms_type, esi.license_key,
                CASE 
                    WHEN esi.sms_template_id = '33' THEN sc.amount
                    WHEN esi.sms_template_id = '64' THEN so.amount
                    WHEN esi.sms_template_id = '65' THEN st.amount
                    WHEN esi.sms_template_id = '68' THEN sm.amount
                END as amount,
                bt.`amount`/100 as `return_amount`,
                bt.operation_date as refunded_at,
                CASE 
                    WHEN esi.sms_template_id = '33' THEN sc.amount_total_returned
                    WHEN esi.sms_template_id = '64' THEN so.amount_total_returned
                    WHEN esi.sms_template_id = '65' THEN st.amount_total_returned
                    WHEN esi.sms_template_id = '68' THEN sm.amount_total_returned
                END as amount_total_returned
            FROM s_extra_services_informs esi
            LEFT JOIN s_contracts c ON c.order_id = esi.order_id
            LEFT JOIN s_users u ON u.id = esi.user_id
            LEFT JOIN s_managers m ON m.id = esi.manager_id
            LEFT JOIN s_orders o ON o.id = esi.order_id
            LEFT JOIN (
                SELECT order_id, amount, type, reference, operation_date
                FROM b2p_transactions
                WHERE type IN (
                    'REFUND_MULTIPOLIS', 'RECOMPENSE_MULTIPOLIS',
                    'REFUND_TV_MEDICAL', 'RECOMPENSE_TV_MEDICAL',
                    'REFUND_CREDIT_DOCTOR', 'RECOMPENSE_CREDIT_DOCTOR',
                    'REFUND_STAR_ORACLE', 'RECOMPENSE_STAR_ORACLE'
                )
            ) bt ON bt.order_id = esi.order_id
            LEFT JOIN s_credit_doctor_to_user sc ON bt.reference = sc.transaction_id
                AND esi.sms_template_id = '33'
                AND sc.STATUS = ?
                AND bt.type IN ('REFUND_CREDIT_DOCTOR', 'RECOMPENSE_CREDIT_DOCTOR')
            LEFT JOIN s_star_oracle so ON bt.reference = so.transaction_id
                AND esi.sms_template_id = '64'
                AND so.STATUS = ?
                AND bt.type IN ('REFUND_STAR_ORACLE', 'RECOMPENSE_STAR_ORACLE')
            LEFT JOIN s_tv_medical_payments st ON bt.reference = st.payment_id
                AND esi.sms_template_id = '65'
                AND st.STATUS = ?
                AND bt.type IN ('REFUND_TV_MEDICAL', 'RECOMPENSE_TV_MEDICAL')
            LEFT JOIN s_multipolis sm ON bt.reference = sm.payment_id
                AND esi.sms_template_id = '68'
                AND sm.STATUS = ?
                AND bt.type IN ('REFUND_MULTIPOLIS', 'RECOMPENSE_MULTIPOLIS')
            WHERE DATE(esi.created_at) BETWEEN ? AND ?
            ORDER BY esi.id DESC
            LIMIT ? OFFSET ?",
                $this->multipolis::STATUS_SUCCESS,
                $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
                $this->star_oracle::STAR_ORACLE_STATUS_SUCCESS,
                $this->dateFrom,
                $this->dateTo,
                self::PAGE_CAPACITY,
                $offset
            );

            $services = $this->db->results();
        } catch (Exception $e) {
            error_log("DB query failed: " . $e->getMessage());
        }

        return $this->calculateRefunds($services);
    }

    /**
     * @throws Exception
     */
    private function getAllResults(): array
    {
        $services = [];

        try {
            $this->db->query("
            SELECT
                u.lastname, u.firstname, u.patronymic,
                TIMESTAMPDIFF(YEAR, STR_TO_DATE(u.birth, '%d.%m.%Y'), CURDATE()) AS age,
                CASE
                    WHEN o.first_loan = o.have_close_credits THEN 'N/A'
                    WHEN o.first_loan THEN 'НК'
                    WHEN o.have_close_credits THEN 'ПК'
                END AS client_type,
                esi.user_id, esi.order_id,
                COALESCE(esi.contract, c.number) AS contract,
                TIMESTAMPDIFF(DAY, c.create_date, CURDATE()) AS days_passed,
                m.name AS manager_name, m.id AS manager_id,
                esi.service_name, esi.sms_template_id,
                DATE(esi.created_at) AS sent_at_date,
                TIME(esi.created_at) AS sent_at_time,
                esi.sms_type, esi.license_key,
                CASE 
                    WHEN esi.sms_template_id = '33' THEN sc.amount
                    WHEN esi.sms_template_id = '64' THEN so.amount
                    WHEN esi.sms_template_id = '65' THEN st.amount
                    WHEN esi.sms_template_id = '68' THEN sm.amount
                END as amount,
                bt.`amount`/100 as `return_amount`,
                bt.operation_date as refunded_at,
                CASE 
                    WHEN esi.sms_template_id = '33' THEN sc.amount_total_returned
                    WHEN esi.sms_template_id = '64' THEN so.amount_total_returned
                    WHEN esi.sms_template_id = '65' THEN st.amount_total_returned
                    WHEN esi.sms_template_id = '68' THEN sm.amount_total_returned
                END as amount_total_returned
            FROM s_extra_services_informs esi
            LEFT JOIN s_contracts c ON c.order_id = esi.order_id
            LEFT JOIN s_users u ON u.id = esi.user_id
            LEFT JOIN s_managers m ON m.id = esi.manager_id
            LEFT JOIN s_orders o ON o.id = esi.order_id
            LEFT JOIN (
                SELECT order_id, amount, type, reference, operation_date
                FROM b2p_transactions
                WHERE type IN (
                    'REFUND_MULTIPOLIS', 'RECOMPENSE_MULTIPOLIS',
                    'REFUND_TV_MEDICAL', 'RECOMPENSE_TV_MEDICAL',
                    'REFUND_CREDIT_DOCTOR', 'RECOMPENSE_CREDIT_DOCTOR',
                    'REFUND_STAR_ORACLE', 'RECOMPENSE_STAR_ORACLE'
                )
            ) bt ON bt.order_id = esi.order_id
            LEFT JOIN s_credit_doctor_to_user sc ON bt.reference = sc.transaction_id
                AND esi.sms_template_id = '33'
                AND sc.STATUS = ?
                AND bt.type IN ('REFUND_CREDIT_DOCTOR', 'RECOMPENSE_CREDIT_DOCTOR')
            LEFT JOIN s_star_oracle so ON bt.reference = so.transaction_id
                AND esi.sms_template_id = '64'
                AND so.STATUS = ?
                AND bt.type IN ('REFUND_STAR_ORACLE', 'RECOMPENSE_STAR_ORACLE')
            LEFT JOIN s_tv_medical_payments st ON bt.reference = st.payment_id
                AND esi.sms_template_id = '65'
                AND st.STATUS = ?
                AND bt.type IN ('REFUND_TV_MEDICAL', 'RECOMPENSE_TV_MEDICAL')
            LEFT JOIN s_multipolis sm ON bt.reference = sm.payment_id
                AND esi.sms_template_id = '68'
                AND sm.STATUS = ?
                AND bt.type IN ('REFUND_MULTIPOLIS', 'RECOMPENSE_MULTIPOLIS')
            WHERE DATE(esi.created_at) BETWEEN ? AND ?
            ORDER BY esi.id",
                $this->multipolis::STATUS_SUCCESS,
                $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
                $this->star_oracle::STAR_ORACLE_STATUS_SUCCESS,
                $this->dateFrom,
                $this->dateTo
            );

            $services = $this->db->results();
        } catch (Exception $e) {
            error_log("DB query failed: " . $e->getMessage());
        }

        return $this->calculateRefunds($services);
    }

    private function getTotals(): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(id) AS total 
            FROM s_extra_services_informs 
            WHERE DATE(created_at) BETWEEN ? AND ?",
            $this->dateFrom, $this->dateTo
        );

        $this->db->query($query);

        return (int) $this->db->result('total');
    }

    private function calculateRefunds(array $services): array
    {
        foreach($services as &$service) {
            $refund_percent = 'неизвестно';
            $refund_amount = $service->return_amount;

            $epsilon = 1;

            if (!empty($service->return_amount)) {
                if (abs($service->return_amount - $service->amount) < $epsilon) {
                    $refund_percent = '100%';
                } elseif (abs($service->return_amount - ($service->amount * 0.75)) < $epsilon) {
                    $refund_percent = '75%';
                } elseif (abs($service->return_amount - ($service->amount * 0.5)) < $epsilon) {
                    $refund_percent = '50%';
                } elseif (abs($service->return_amount - ($service->amount * 0.25)) < $epsilon) {
                    $refund_percent = '25%';
                }
            }

            $service->refund_amount = number_format($refund_amount, 2, '.', '');
            $service->refund_percent = $refund_percent;
        }

        return $services;
    }

    /**
     * @throws Exception
     */
    private function download(): void
    {
        $maxPeriod = 365; // 1 год в днях

        $dateFromTimestamp = strtotime($this->dateFrom);
        $dateToTimestamp = strtotime($this->dateTo);
        $diffInDays = ($dateToTimestamp - $dateFromTimestamp) / (60 * 60 * 24);

        // Проверка, что выбранный диапазон не превышает 1 год
        if ($diffInDays > $maxPeriod) {
            $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
        }

        $header = [
            'Клиент, возраст' => 'string',
            'Тип клиента' => 'string',
            'Договор' => 'string',
            'Дней с заключения договора' => 'string',
            'Менеджер' => 'string',
            'Дата отправки SMS' => 'string',
            'Время отправки SMS' => 'string',
            'Услуга' => 'string',
            'Тип SMS' => 'string',
            'Лицензионный ключ' => 'string',
            'Дата возврата' => 'string',
            'Процент' => 'string',
            'Сумма' => 'price',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        $items = $this->getAllResults();

        foreach ($items as $item) {
            $client = trim("{$item->lastname} {$item->firstname} {$item->patronymic}, {$item->age} лет");

            $writer->writeSheetRow('Отчёт', [
                $client,
                $item->client_type,
                $item->contract,
                $item->days_passed,
                $item->manager_name,
                $item->sent_at_date,
                $item->sent_at_time,
                $item->service_name,
                $item->sms_type,
                $item->license_key,
                $item->refunded_at,
                $item->refund_percent,
                $item->refund_amount
            ]);
        }

        $filename = 'extra_services_informs_report_' . date('Y-m-d') . '.xlsx';

        // Отправка файла для загрузки
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }
}
