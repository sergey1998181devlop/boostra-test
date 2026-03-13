<?php

require_once 'View.php';

class PaymentsClientsReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private string $dateFrom;
    private string $dateTo;

    private const SERVICE_PAYMENT_METHOD = 'B2P';
    private const SERVICE_TITLE = [
        "s_credit_doctor_to_user" => 'Кредитный доктор',
        "s_multipolis" => 'Консьерж сервис',
        "s_tv_medical_payments" => 'Вита-мед',
    ];

    private const SERVICE_REFUNDS = [
        "additional_service_repayment" => 'Кредитный доктор',
        "additional_service_multipolis" => 'Консьерж сервис',
        "additional_service_tv_med" => 'Вита-мед',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();
        $this->totalItems = $this->getTotals();
        $this->pagesNum = (int)ceil($this->totalItems / static::PAGE_CAPACITY);

        $this->handleAction();
    }

    private function setupDateRange(): void
    {
        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('-1 months')) . ' - ' . date('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);
        $this->dateFrom = date('Y-m-d', strtotime($from));
        $this->dateTo = date('Y-m-d', strtotime($to));
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
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
        ));

        return $this->design->fetch('payments_clients_report.tpl');
    }

    private function getResults(int $currentPage = 0)
    {
        $offset = static::PAGE_CAPACITY * ($currentPage - 1);
        if ($currentPage == 0) {
            $offset = 0;
        }

        $query = $this->db->placehold("
            SELECT
                b2p.created AS created,
                b2p.user_id AS user_id,
                b2p.order_id AS order_id,
                b2p.contract_number AS contract_number,
                b2p.amount as amount,
                b2p.prolongation as prolongation,
                CONCAT(u.lastname, ' ', u.firstName, ' ', u.patronymic) as user_fio,
                u.phone_mobile as user_phone,
                tv.price as additional_service_tv_med_price,
                COALESCE(tv_medical.amount, 0) as tv_medical_amount,
                multipolis.return_amount as additional_service_multipolis_price,
                COALESCE(multipolis.amount, 0) as multipolis_amount,
                credit_doctor_to_user.return_amount as additional_service_repayment_price,
                COALESCE(credit_doctor_to_user.amount, 0) as credit_doctor_to_user_amount,
                CONCAT(managers.lastname, ' ', managers.firstName, ' ', managers.patronymic) as support_fio,
                COALESCE(mytickets.updated_at, mytickets.created_at) as support_last_update
            FROM b2p_payments b2p
                LEFT JOIN s_tv_medical_payments tv_medical ON tv_medical.payment_id = b2p.id AND tv_medical.payment_method = '" . static::SERVICE_PAYMENT_METHOD . "'
                LEFT JOIN s_tv_medical tv ON tv_medical.tv_medical_id = tv.id
                LEFT JOIN s_multipolis multipolis ON multipolis.payment_id = b2p.id AND multipolis.payment_method = '" . static::SERVICE_PAYMENT_METHOD . "'
                LEFT JOIN b2p_transactions b2pt ON b2pt.order_id = b2p.order_id AND b2pt.user_id = b2p.user_id AND b2pt.contract_number = b2p.contract_number
                LEFT JOIN s_credit_doctor_to_user credit_doctor_to_user ON b2pt.reference = credit_doctor_to_user.transaction_id
                LEFT JOIN s_users u ON b2p.user_id = u.id
                LEFT JOIN s_mytickets AS mytickets ON u.id = mytickets.client_id AND b2p.order_id = mytickets.order_id
                LEFT JOIN s_users managers ON mytickets.manager_id = managers.id
            WHERE (b2p.created BETWEEN ? AND ?)
            AND (b2pt.created BETWEEN ? AND ?)
            ORDER BY b2p.created DESC
            LIMIT ? OFFSET ?",
            $this->dateFrom, $this->dateTo, $this->dateFrom, $this->dateTo, ($currentPage == 0) ? $this->getTotals() : static::PAGE_CAPACITY, $offset
        );

        $this->db->query($query);

        $result = $this->db->results();
        foreach ($result as $key => $item) {
            $result[$key]->refund_service = [];
            $result[$key]->refund_manager = '';
            $result[$key]->refund_created = [];
            $result[$key]->refund_sum = 0;
            $query = $this->db->placehold("
                SELECT 
                    managers.id,
                    CONCAT(managers.lastname, ' ', managers.firstName, ' ', managers.patronymic) as manager_fio,
                    changelogs.type,
                    changelogs.created as created
                FROM s_changelogs changelogs
                LEFT JOIN s_order_data order_data ON order_data.order_id = changelogs.order_id AND changelogs.type = order_data.key
                LEFT JOIN s_users managers ON changelogs.user_id = managers.id
                WHERE changelogs.order_id = ? AND order_data.value = 0
            ", $item->order_id);

            $this->db->query($query);
            $res = $this->db->results();
            if (!empty($res)) {
                foreach ($res as $row) {
                    if (in_array($row->type, array_keys(static::SERVICE_REFUNDS))) {
                        $result[$key]->refund_service[] = static::SERVICE_REFUNDS[$row->type];
                        $result[$key]->refund_manager = $row->manager_fio;
                        $result[$key]->refund_created[] = $row->created;
                        $result[$key]->refund_sum .= $row->{$row->type} . '_price';
                    }
                }
            }
        }

        return $result;
    }

    private function getTotals(): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(b2p.id) AS total 
            FROM b2p_payments b2p
            LEFT JOIN b2p_transactions b2pt ON b2pt.order_id = b2p.order_id AND b2pt.user_id = b2p.user_id AND b2pt.contract_number = b2p.contract_number
            LEFT JOIN s_credit_doctor_to_user credit_doctor_to_user ON b2pt.reference = credit_doctor_to_user.transaction_id
            WHERE (b2p.created BETWEEN ? AND ?)
            AND (b2pt.created BETWEEN ? AND ?)
            ",
            $this->dateFrom, $this->dateTo, $this->dateFrom, $this->dateTo
        );

        $this->db->query($query);

        return (int) $this->db->result('total');
    }

    /**
     * @throws Exception
     */
    private function download(): void
    {
        $header = [
            '№ договора' => 'string',
            'Дата платежа' => 'string',
            'Фио клиента' => 'string',
            'Сумма оплаты' => 'string',
            'Тип оплаты' => 'string',
            'Номер телефона клиента' => 'string',
            'Дата контакта с горячей линией' => 'string',
            'Последний контакт' => 'string',
            'Информация о доп подключенном' => 'string',
            'Дата отключения доп' => 'string',
            'Фио кто отключил' => 'string',
            'Сумма доп услуги подключенной' => 'string',
            'Сумма доп услуги отключенной' => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        $items = $this->getResults();

        foreach ($items as $item) {
            $dops = [];
            if (!empty($item->tv_medical_amount)) {
                $dops = 'Вита-мед';
            }
            if (!empty($item->multipolis_amount)) {
                $dops = 'Консьерж сервис';
            }
            if (!empty($item->credit_doctor_to_user_amount)) {
                $dops = 'Кредитный доктор';
            }

            $writer->writeSheetRow('Отчёт', [
                $item->contract_number,
                $item->created,
                $item->user_fio,
                $item->amount,
                ($item->prolongation) ? 'Продление' : 'Погашение',
                $item->user_phone,
                $item->support_last_update,
                $item->support_fio,
                implode(', ', $dops),
                implode(',', $item->refund_service) . ' ' . implode(',', $item->refund_created),
                $item->refund_manager,
                $item->tv_medical_amount + $item->multipolis_amount + $item->credit_doctor_to_user_amount,
                $item->refund_sum ?? 0,
            ]);
        }

        $filename = 'dormant_clients_report_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }
}
