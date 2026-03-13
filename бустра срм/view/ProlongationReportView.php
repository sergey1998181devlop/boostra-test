<?php

require_once 'lib/autoloader.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';
require_once 'View.php';


class ProlongationReportView extends View
{
    /**
     * Лимит
     */
    public const PAGE_CAPACITY = 15;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @var int
     */
    private $pagesNum;

    private $date_from;

    private $date_to;

    const NK_USER = 'НК';

    const PK_USER = 'ПК';

    const ON_SERVICE = 'Включение';

    private $order_statuses;

    private $organizations_list;

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer'));

        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('- 1 month')) . ' - ' . date('d.m.Y');
        }

        list($from, $to) = explode('-', $daterange);
        $this->date_from = date('Y-m-d', strtotime($from));
        $this->date_to = date('Y-m-d', strtotime($to));

        $this->design->assign('date_from', $this->date_from);
        $this->design->assign('date_to', $this->date_to);
        $this->design->assign('from', $from);
        $this->design->assign('to', $to);

        $this->totalItems = $this->getTotals();
        $this->pagesNum = ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->order_statuses = [
            'prolongation' => 'Пролонгация',
            $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE => 'Выдача',
            $this->order_data::ADDITIONAL_SERVICE_REPAYMENT => 'Закрытие',
            $this->order_data::HALF_ADDITIONAL_SERVICE_REPAYMENT => 'Закрытие 50%',
            $this->order_data::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT => 'Частичное закрытие',
            $this->order_data::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT => 'Частичное закрытие 50%',
            $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT => 'ЗО на закрытии',
            $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT => 'ЗО на закрытии 50%',
            $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT => 'ЗО на частичном закрытии',
            $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT => 'ЗО на частичном закрытии 50%',
        ];

        $this->design->assign('order_statuses', $this->order_statuses);

        $this->organizations_list = $this->organizations->getList();
        $this->design->assign('organizations', $this->organizations_list);

        $action = $this->request->get('action');
        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        $filters = [
            'organizations' => $this->request->get('organizations') ?? null,
            'products' => $this->request->get('products') ?? null,
            'types' => $this->request->get('types') ?? null,
            'nk_pk' => $this->request->get('nk_pk') ?? null,
        ];

        $items = $this->getResults($this->currentPage, $filters);
        if (!empty($filters['organizations']) || !empty($filters['products']) || !empty($filters['types']) || !empty($filters['nk_pk'])) {
            $items = $this->getResults(1, $filters, true);
            $this->totalItems = count($items);
        } else {
            $this->totalItems = $this->getTotals();
        }

        $this->pagesNum = ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->design->assign('items', $items);
        $this->design->assign('current_page_num', $this->currentPage);
        $this->design->assign('total_pages_num', $this->pagesNum);
        $this->design->assign('total_items', $this->totalItems);

        $this->design->assign('reportUri', strtok($_SERVER['REQUEST_URI'], '?'));

        $this->design->assign('organizations_filter', $filters['organizations']);
        $this->design->assign('products', $filters['products']);
        $this->design->assign('types', $filters['types']);
        $this->design->assign('nk_pk', $filters['nk_pk']);

        $this->design->assign('can_see_manager_url', in_array('verificators', $this->manager->permissions));
        $this->design->assign('can_see_client_url', in_array('clients', $this->manager->permissions));

        return $this->design->fetch('prolongation_report.tpl');
    }

    /**
     * Генерация данных
     *
     * @param int $current_page
     * @param array $filter
     * @param bool $all
     * @return array
     */
    private function getResults(int $current_page, array $filter = [], bool $all = false): array
    {
        $andWhere = '';
        if (!empty($filter['organizations'])) {
            $andWhere .= ' AND org.id = ' . $filter['organizations'];
        }

        if (!empty($filter['products'])) {
            if ($filter['products'] === 'financial_doctor') {
                $andWhere .= ' AND cl.`type` = \'' . $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE . '\'';
            } elseif ($filter['products'] === 'so_repayment') {
                $listSoRepayments = [
                    $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT,
                    $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
                    $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
                    $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
                ];
                $andWhere .= " AND cl.`type` IN ('" . implode("','", $listSoRepayments) . "')";
            } else {
                $andWhere .= ' AND cl.`type` = \'' . $filter['products'] . '\'';
            }
        }

        if (!empty($filter['types'])) {
            if ($filter['types'] == 'prolongation') {
                $andWhere .= ' AND (cl.`type` = \'' . $this->order_data::ADDITIONAL_SERVICE_TV_MED . '\' OR cl.`type` = \'' . $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS . '\')';
            } elseif ($filter['types'] == 'disable_additional_service_on_issue') {
                $andWhere .= ' AND cl.`type` = \'' . $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE . '\'';
            } else {
                $andWhere .= ' AND cl.`type` = \'' . $filter['types'] . '\'';
            }
        }

        $offset = self::PAGE_CAPACITY * ($current_page - 1);

        $query = $this->db->placehold("
            SELECT 
            u.lastname, u.firstname, u.patronymic,
            (
                SELECT cancellation_additional_services_by_phone
                FROM s_orders o 
                WHERE o.user_id = u.id 
                  AND (o.status = 10 OR o.1c_status = '5.Выдан') 
                ORDER BY id DESC 
                LIMIT 1
            ) as cancellation_additional_services_by_phone, 
            (
                SELECT case when have_close_credits = 1 then 'PK' else 'NK' end
                FROM s_orders o 
                WHERE o.user_id = u.id 
                    AND (o.status = 10 OR o.1c_status = '5.Выдан') 
                ORDER BY id DESC 
                LIMIT 1
            ) as user_type, 
            u.birth,
            u.id AS user_id,
            c.number AS contract,
            org.short_name AS organization,
            m.name AS manager_name,
            m.id AS manager_id,
            cl.type,
            cl.new_values, 
            cl.created,
            DATEDIFF(STR_TO_DATE(cl.created, '%Y-%m-%d'), STR_TO_DATE(o.date, '%Y-%m-%d')) AS off_days_count
            FROM s_changelogs cl 
            LEFT JOIN s_users u ON u.id = cl.user_id
            LEFT JOIN s_contracts c ON c.order_id = cl.order_id
            LEFT JOIN s_managers m ON m.id = cl.manager_id 
            LEFT JOIN s_organizations org ON org.id = c.organization_id 
            LEFT JOIN s_orders o ON o.id = cl.order_id
            WHERE cl.`type` IN (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                AND cl.old_values = ? 
                AND DATE(cl.created) >= ? AND DATE(cl.created) <= ? 
            " . $andWhere . "
            ORDER BY cl.id DESC
            LIMIT ? OFFSET ?",
            $this->order_data::ADDITIONAL_SERVICE_TV_MED,
            $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS,
            $this->order_data::ADDITIONAL_SERVICE_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_REPAYMENT,
            $this->order_data::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
            $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE,
            $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
            $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
            static::ON_SERVICE,
            $this->date_from,
            $this->date_to,
            ($all) ? $this->totalItems : self::PAGE_CAPACITY,
            ($all) ? 0 : $offset
        );

        $this->db->query($query);

        $results = $this->db->results();

        if (!empty($filter['nk_pk'])) {
            $results = array_filter($results, function ($obj) use ($filter) {
                return $obj->user_type == $filter['nk_pk'];
            });
        }

        return $results;
    }

    /**
     * Получаем итого
     *
     * @return int
     */
    private function getTotals(array $filters = []): int
    {
        $andWhere = '';
        if (!empty($filter['organizations'])) {
            $andWhere .= ' AND org.id = ' . $filter['organizations'];
        }

        if (!empty($filter['products'])) {
            if ($filter['products'] === 'financial_doctor') {
                $andWhere .= ' AND cl.`type` = \'' . $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE . '\'';
            } elseif ($filter['products'] === 'so_repayment') {
                $listSoRepayments = [
                    $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT,
                    $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
                    $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
                    $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
                ];
                $andWhere .= " AND cl.`type` IN ('" . implode("','", $listSoRepayments) . "')";
            } else {
                $andWhere .= ' AND cl.`type` = \'' . $filter['products'] . '\'';
            }
        }

        if (!empty($filter['types'])) {
            if ($filter['types'] == 'prolongation') {
                $andWhere .= ' AND (cl.`type` = \'' . $this->order_data::ADDITIONAL_SERVICE_TV_MED . '\' OR cl.`type` = \'' . $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS . '\')';
            } elseif ($filter['types'] == 'disable_additional_service_on_issue') {
                $andWhere .= ' AND cl.`type` = \'' . $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE . '\'';
            } else {
                $andWhere .= ' AND cl.`type` = \'' . $filter['types'] . '\'';
            }
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS total 
            FROM s_changelogs cl 
            WHERE cl.`type` IN (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                AND cl.old_values = ? 
                AND DATE(cl.created) >= ? AND DATE(cl.created) <= ? 
            " . $andWhere . "
            ",
            $this->order_data::ADDITIONAL_SERVICE_TV_MED,
            $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS,
            $this->order_data::ADDITIONAL_SERVICE_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_REPAYMENT,
            $this->order_data::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
            $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE,
            $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
            $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
            $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
            static::ON_SERVICE,
            $this->date_from,
            $this->date_to
        );

        $this->db->query($query);

        return $this->db->result('total');
    }

    /**
     * Выгрузка данных в Excel
     *
     * @return void
     */
    private function download(): void
    {
        ini_set('max_execution_time', 12000);
        ini_set('memory_limit', '-1');
        require dirname(__DIR__) . '/vendor/autoload.php';

        $header = [
            'Клиент' => 'string',
            'Дата рождения' => 'string',
            'НК/ПК' => 'string',
            'Договор' => 'string',
            'Организация' => 'string',
            'ФИО менеджера' => 'string',
            'Продукт' => 'string',
            'Тип' => 'string',
            'Действие' => 'string',
            'День отключения' => 'integer',
            'Дата отключения' => 'string',
            'Время отключения' => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('prolongation_report', $header);

        $allItems = $this->getResults(1, [], true);
        foreach ($allItems as $item) {
            $fio = $item->firstname . ' ' . $item->lastname;
            if (!empty($item->patronymic)) {
                $fio .= ' ' . $item->patronymic;
            }

            $created_date = date_create($item->created);
            switch ($item->type) {
                case $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS:
                    $product = 'Консьерж сервис';
                    break;
                case $this->order_data::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE:
                    $product = 'Финансовый доктор';
                    break;
                case $this->order_data::ADDITIONAL_SERVICE_SO_REPAYMENT:
                case $this->order_data::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT:
                case $this->order_data::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT:
                case $this->order_data::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT:
                    $product = 'Звездный оракул';
                    break;
                default:
                    $product = 'Вита-мед';
                    break;
            }

            $type = $this->order_statuses[$item->type] ?? $this->order_statuses['prolongation'];

            $row_data = [
                $fio,
                $item->birth,
                ($item->user_type == 'NK') ? static::NK_USER : static::PK_USER,
                $item->contract,
                $item->organization,
                $item->manager_name,
                $product,
                $type,
                $item->new_values,
                $item->off_days_count,
                date_format($created_date, 'd.m.Y'),
                date_format($created_date, 'H:i:s'),
            ];

            $writer->writeSheetRow('prolongation_report', $row_data);
        }

        $filename = 'files/reports/prolongation_report.xlsx';
        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location:' . $filename);
        exit;
    }
}
