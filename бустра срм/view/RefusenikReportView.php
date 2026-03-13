<?php

#ini_set('display_errors', 1);
#error_reporting(E_ALL);

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';

/**
 * Class RejectReportView
 * Класс для работы с отчётом по отказикам
 */
class RefusenikReportView extends View
{
    /**
     * Шаблон отчета
     * @const string
     */
    public const TEMPLATE = 'refusenik_report_view.tpl';

    /**
     * Лимит
     *
     * @const int
     */
    public const PAGE_CAPACITY = 20;
    /**
     * Значения фильтров для ПК
     */
    public const CLIENT_FILTERS = [
        'NK',
        'PK',
        'ALL',
    ];
    public const ONDATE_FILTERS =[
        'ORDER',
        'REPORT'
    ];
    public const SCORISTA_FILTERS =[
        '0',
        '200',
        '400'
    ];

    /**
     * @var int
     */
    private $current_page;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $pages_num;

    /**
     * Массив с фильтрами
     * @var array
     */
    private array $filter_data = [];

    /**
     * Результирующий массив
     * @var array
     */
    private array $results = [];


    public function __construct()
    {
        parent::__construct();
        // Обработка входных параметров и инициализация параметров для работы
        $this->filter_data = $this->getFilterData();
        $this->total        = $this->getTotals();
        $this->pages_num    = max(1,ceil( $this->total / self::PAGE_CAPACITY ));
        $this->current_page =  min( $this->pages_num , max( 1, $this->request->get( 'page', 'integer' )) );
        $action = $this->request->get('action');
        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    public function fetch()
    {
        $this->design->assign('date_from', $this->filter_data['filter_date_order']['filter_date_start']);
        $this->design->assign('date_to', $this->filter_data['filter_date_order']['filter_date_end']);
        $this->design->assign('from', $this->filter_data['filter_date_order']['filter_date_start_dot']);
        $this->design->assign('to', $this->filter_data['filter_date_order']['filter_date_end_dot']);
        if ( $this->request->get('ajax') ) {
            $this->results = $this->getRawData( false,( $this->current_page - 1 ) * self::PAGE_CAPACITY, self::PAGE_CAPACITY );
            $this->design->assign('items', $this->results );
            $this->design->assign('results', $this->results);
            $this->design->assign('total_results', $this->total);
            $this->design->assign( 'current_page_num', $this->current_page );
            $this->design->assign( 'total_pages_num', $this->pages_num );
            $this->design->assign( 'total_items', $this->total );
            $this->design->assign( 'reportUri', strtok( $_SERVER['REQUEST_URI'], '?' ) );

            $this->design->assign( 'filter_client', $this->filter_data['filter_client'] );
            $this->design->assign( 'filter_ondate', $this->filter_data['filter_ondate'] );
            $this->design->assign( 'filter_scorista', $this->filter_data['filter_scorista'] );
            $this->design->assign( 'filter_client', $this->filter_data['filter_client'] );
            $this->design->assign( 'filter_remove_dublicate_by_phone', $this->filter_data['filter_remove_dublicate_by_phone'] );
            $this->design->assign( 'filter_order_valid', $this->filter_data['filter_order_valid'] );
        }
        return $this->design->fetch(self::TEMPLATE);
    }

    /**
     * Выбор фильтров
     * @return array
     */
    private function getFilterData(): array
    {
        return  [
            'filter_date_order' =>  $this->getDataRange(),
            'filter_client' => in_array($this->request->get('filter_client'), self::CLIENT_FILTERS) ? $this->request->get('filter_client') : 'NK',
            'filter_ondate' => in_array($this->request->get('filter_ondate'), self::ONDATE_FILTERS) ? $this->request->get('filter_ondate') : 'ORDER',
            'filter_scorista' =>  in_array($this->request->get('filter_scorista'), self::SCORISTA_FILTERS) ? $this->request->get('filter_scorista') : '0',
            'filter_order_valid' => $this->request->get('filter_order_valid', 'boolean'),
            'filter_remove_dublicate_by_phone' => $this->request->get('filter_remove_dublicate_by_phone', 'boolean'),
            'page' => $this->request->get('filter_remove_dublicate_by_phone', 'boolean'),
        ];

    }

    /**
     * Возвращает общее количество полученных данных
     *
     * @param int $offset
     * @param int $amount
     *
     * @return int
     * @throws Exception
     */
    private function getTotals(): int
    {
        return $this->getRawData()[0]->total ?? 0;
    }


    /**
     * Получение данных
     * @param bool $get_total_count
     * @param int $offset
     * @param int $amount
     *
     * @return array
     * @throws Exception
     */
    private function getRawData( bool $get_total_count = true, int $offset = 0, int $amount = 0 ): array
    {

        if (
            (!isset($this->filter_data['filter_client'])) OR
            (!isset($this->filter_data['filter_scorista'])) OR
            (!isset($this->filter_data['filter_date_order']['filter_date_start'])) OR
            (!isset($this->filter_data['filter_date_order']['filter_date_end']))
        ) return [];

        $filter_date = $this->db->placehold("AND o.date >= ? AND o.date <= ?",$this->filter_data['filter_date_order']['filter_date_start'],$this->filter_data['filter_date_order']['filter_date_end']) ;
        $select_limit = ($amount) ? "LIMIT {$offset},{$amount}" : "";
        $select_fields = "
                 u.`phone_mobile`,
                 o.`id`,
                 o.`user_id`,
                 CONCAT_WS(' ',u.lastname,u.firstname,u.patronymic) AS `fio`,
                 r.`admin_name` AS `reason`,
                 o.`scorista_ball`,
                 o.`date`
            ";

        if ($get_total_count) {
            $select_fields = "
                COUNT(o.`id`) as `total`
            ";
            $select_limit = "";
        }

        // Если фильтр на дату отчета, то берем всех клиентов НК+ПК и фильтруем уже после выборки
        $sql_filter_ondate = "";
        $filter_client = "";
        switch ($this->filter_data['filter_client']) {
            case 'NK':
                $filter_client = "AND o.have_close_credits = 0";
                if ($this->filter_data['filter_ondate'] == 'REPORT') {
                    $sql_filter_ondate = $this->db->placehold("
                       AND NOT EXISTS
                        (SELECT o2.id 
                            FROM s_orders o2
                            WHERE 
                                o.user_id= o2.user_id AND
                                o2.have_close_credits = 1 AND
                                o2.date<=?
                        )",$this->filter_data['filter_date_order']['filter_date_end']);
                    $filter_client = "";
                }
                break;
            case 'PK':
                $filter_client = "AND o. have_close_credits = 1";
                if ($this->filter_data['filter_ondate'] == 'REPORT') {
                    $sql_filter_ondate = $this->db->placehold("
                       AND EXISTS
                        (SELECT o2.id 
                            FROM s_orders o2
                            WHERE 
                                o.user_id= o2.user_id AND
                                o2.have_close_credits = 1 AND
                                o2.date<=?
                        )",$this->filter_data['filter_date_order']['filter_date_end']);
                    $filter_client = "";
                }
                break;
            case 'ALL':
                $filter_client = "";
                break;
        }

        $filter_scorista = "";
        switch ($this->filter_data['filter_scorista']) {
            case '0':
                $filter_scorista = "AND o.scorista_ball < 200 ";
                break;
            case '200':
                $filter_scorista = "AND o.scorista_ball >= 200 AND o.scorista_ball < 400";
                break;
            case '400':
                $filter_scorista = "AND o.scorista_ball >= 400";
                break;
        }



        $filter_order_valid  = "";
        if ($this->filter_data['filter_order_valid']) {
            $filter_order_valid = "   
                AND EXISTS
                (SELECT `user_id` FROM `s_orders` AS o4
                    WHERE 
                      o.user_id = o4.user_id AND
                      o4.1c_status='5.Выдан'
                 )	
			";
        }

        // Для удаления дубликатов используется промежуточная выборка
        $filter_maxid_line_by_phone = "";
        if ($this->filter_data['filter_remove_dublicate_by_phone']) {
            $query = $this->db->placehold("
            SELECT MAX(`id`) as `maxid` FROM (
                SELECT 
                    u.`phone_mobile`, o.`id`
                FROM __orders o
                LEFT JOIN __reasons r 
                    ON o.`reason_id`=r.`id`
                LEFT JOIN __users u 
                    ON o.`user_id`=u.`id`	
                WHERE 
                    (o.status=3 OR o.status=4 OR o.status=11)
                    {$filter_client}
                    {$filter_date}
                    {$filter_scorista}
                    {$sql_filter_ondate}
                    {$filter_order_valid}
                ORDER BY o.`date`
            ) as `group_table`
            GROUP BY `phone_mobile`");

            $this->db->query($query);
            $filter_maxid_line_by_phone = implode(', ', array_map(function($c) {
                return $c->maxid;
            }, $this->db->results() ));
            $filter_maxid_line_by_phone = $filter_maxid_line_by_phone ?  "AND o.id IN (".$filter_maxid_line_by_phone.")" : 'AND (1=0) ';
            $filter_client  = "";
            $filter_date = "";
            $filter_scorista = "";
            $sql_filter_ondate = "";
            $filter_order_valid = "";
        }       
        
        
        $query = $this->db->placehold("
            SELECT 
                 {$select_fields}
            FROM __orders o
            LEFT JOIN __reasons r 
                ON o.`reason_id`=r.`id`
            LEFT JOIN __users u 
                ON o.`user_id`=u.`id`	
            WHERE 
                (o.status=3 OR o.status=4 OR o.status=11)
                {$filter_maxid_line_by_phone}
                {$filter_client}
                {$filter_date}
                {$filter_scorista}
                {$sql_filter_ondate}
                {$filter_order_valid}
            ORDER BY o.`date`
            $select_limit
        ");
        $this->db->query($query);
        return $this->db->results();
    }


    /**
     * Выгрузка данных в Excel
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function download()
    {
        require_once $this->config->root_dir . 'PHPExcel/Classes/PHPExcel.php';
        error_reporting(0);
        ini_set('display_errors', 'Off');
        ini_set( 'max_execution_time', '0' );
        ini_set( 'memory_limit', '-1' );
        require dirname( __DIR__ ) . '/vendor/autoload.php';


        $header = [
            'ФИО клиента'           => 'string',
            'Номер телефона'   => 'string',
            'Дата подачи заявки'      => 'date',
            'Причина отказа'    => 'string',
            'Балл скористы'       => 'string',
        ];

        $this->results = $this->getRawData( false);

        $writer = new XLSXWriter();
        $writer->writeSheetHeader( 'refusenik_report', $header );
        foreach( $this->results as $key => $item ){
            $row_data = [
                $item->fio ,
                $item->phone_mobile,
                $item->date,
                $item->reason,
                $item->scorista_ball,
            ];

            $writer->writeSheetRow( 'refusenik_report', $row_data );
        }

        $filename = 'files/reports/report__refusenik_report__' . date('d.m.Y' ) . '.xlsx';
        $writer->writeToFile( $this->config->root_dir . $filename );
        header( 'Location:' . $filename );
        exit;
    }

    /**
     * Получение дат для фильтра
     * @return array
     */
    public function getDataRange(): array
    {
        $filter_data = [];

        $filter_date_start = date('Y-m-01') . " 0:00:00";
        $filter_date_end = date("Y-m-d"). " 23:59:59";
        $filter_data['filter_date_start_dot'] = date('Y.m.01');
        $filter_data['filter_date_end_dot'] = date("Y.m.d");


        $filter_date_range = $this->request->get('date_range') ?? '';
        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $filter_date_start = str_replace('.', '-', $filter_date_array[0] . " 0:00:00");
            $filter_date_end = str_replace('.', '-', $filter_date_array[1] . " 23:59:59");
            $filter_data['filter_date_start_dot'] = $filter_date_array[0];
            $filter_data['filter_date_end_dot'] = $filter_date_array[1];
        }

        $filter_data['filter_date_start'] = $filter_date_start;
        $filter_data['filter_date_end'] = $filter_date_end;
        return $filter_data;
    }
}
