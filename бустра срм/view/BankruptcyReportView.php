<?php

use boostra\repositories\Repository;
use boostra\services\Core;
use boostra\domains\Scoring;

require_once 'lib/autoloader.php';
require_once dirname( __DIR__ ) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname( __DIR__ ) . '/api/Helpers.php';
require_once 'View.php';


class BankruptcyReportView extends View{
    
    /**
     * Заголовок станицы
     * @var string
     */
    public $title = 'Отчет по банкротствам';
    
    /**
     * Шаблон отчета
     * @var string
     */
    public $template = 'bankruptcy_report.tpl';
    
    /**
     * Лимит
     *
     * @const int
     */
    public const PAGE_CAPACITY = 20;
    
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
     * Колонки, их код, тип и описание
     * @var array
     */
    private $columns = [
        'fio'             => [
            'type'        => 'string',
            'description' => 'ФИО клиента',
            // 'fields'      => [
            //     'user' => [ 'lastname', 'firstname', 'patronymic' ],
            // ],
        ],
        'scoring_date'    => [
            'type'        => 'string',
            'description' => 'Дата проведения скоринга',
            // 'fields'      => ['start_date'],
        ],
        'bankruptcy_date' => [
            'type'        => 'string',
            'description' => 'Дата признания банкротом',
            // 'fields'      => ['bankruptcy_date'],
        ],
    ];
    /**
     * @var mixed|null
     */
    private $download_button_title = 'Выгрузка банкротство';
    
    public function __construct()
    {
        parent::__construct();
        
        // Обработка входных параметров и инициализация параметров для работы
        $this->current_page = max( 1, $this->request->get( 'page', 'integer' ) );
        $this->total        = $this->getTotal();
        $this->pages_num    = ceil( $this->total / self::PAGE_CAPACITY );
        $action             = $this->request->get( 'action' );
        
        // Вызов действия, если существует
        if( method_exists( self::class, $action ) ){
            $this->{$action}();
        }
    }
    
    /**
     * @throws Exception
     */
    public function fetch()
    {
        require_once 'lib/autoloader.php';
        
        // Получение данных
        $raw_data = $this->getRawData( ( $this->current_page - 1 ) * self::PAGE_CAPACITY, self::PAGE_CAPACITY );
        
        $data = [];
        foreach( $raw_data as $raw_item ){
            $data[] = [
                "{$raw_item->user->lastname} {$raw_item->user->firstname} {$raw_item->user->patronymic}",
                $raw_item->created,
                $raw_item->bankruptcy_date,
                $raw_item->user_id,
            ];
        }
        
        $map   = array_keys( $this->columns );
        $map[] = 'user_id';
        $items = $this->combineDataWithColumns( $data, $map );
        
        // Назначение переменных
        $this->design->assign( 'title', $this->title );
        $this->design->assign( 'items', $items );
        $this->design->assign( 'download_button_title', $this->download_button_title );
        $this->design->assign( 'headings', array_column( $this->columns, 'description' ) );
        $this->design->assign( 'current_page_num', $this->current_page );
        $this->design->assign( 'total_pages_num', $this->pages_num );
        $this->design->assign( 'total_items', $this->total );
        $this->design->assign( 'reportUri', strtok( $_SERVER['REQUEST_URI'], '?' ) );
        
        // Подключение шаблона
        return $this->design->fetch( $this->template );
    }
    
    /**
     * Генерация данных
     *
     * @param int|null $current_page
     *
     * @return array
     * @throws Exception
     */
    private function getProcessedData( int $current_page = null ): array
    {
        $raw_data = $current_page
            ? $this->getRawData( ( $current_page - 1 ) * self::PAGE_CAPACITY, self::PAGE_CAPACITY )
            : $this->getRawData();
        $data     = $this->convertDataToColumns( $raw_data );
        $items    = $this->combineDataWithColumns( $data );
        
        return $items;
    }
    
    /**
     * Получение данных
     *
     * @param int $offset
     * @param int $amount
     *
     * @return array
     * @throws Exception
     */
    private function getRawData( int $offset = 0, int $amount = 100000 ): array
    {
        return ( new Repository( \boostra\domains\ScoringEFRSB::class ) )
            ->readBatch(
                [
                    'status'  => ['in', [$this->scorings::STATUS_COMPLETED] ],
                    'success' => 0,
                ],
                'created',
                'desc',
                $offset,
                $amount,
                // ['user_id', 'created', 'bankruptcy_date'],
            );
    }
    
    /**
     * @param \boostra\domains\ScoringEFRSB[] $raw_items
     * @param array|null                      $fields
     *
     * @return array
     */
    private function convertDataToColumns( array $raw_items, array $fields = null ): array
    {
        // $fields = $fields ?? array_column( $this->columns, 'fields' );
        //
        // $data = [];
        // foreach( $raw_items as $raw_item ){
        //
        //     $datum = [];
        //     foreach( $fields as $field_key => $field ){
        //         if( is_array( $field ) ){
        //             $sub_datum = [];
        //             foreach( $field as $sub_field ){
        //                 $sub_datum[] = $raw_item->$field_key->$sub_field;
        //             }
        //             $datum = implode( ' ', $sub_datum );
        //         }else{
        //             $datum[] = $raw_item->$field;
        //         }
        //     }
        //
        //     $data[] = $datum;
        // }
        
        $data = [];
        foreach( $raw_items as $raw_item ){
            $data[] = [
                "{$raw_item->user->lastname} {$raw_item->user->firstname} {$raw_item->user->patronymic}",
                $raw_item->created,
                $raw_item->bankruptcy_date,
            ];
        }
        
        return $data;
    }
    
    /**
     * @param array      $data
     * @param array|null $map
     *
     * @return array
     */
    private function combineDataWithColumns( array $data, array $map = null ): array
    {
        $map = $map ?? array_keys( $this->columns );
        
        return array_map(
            static function( $item ) use ( $map ){
                return (object) array_combine( $map, $item );
            },
            $data
        );
    }
    
    /**
     * Получаем итого
     *
     * @return int
     */
    private function getTotal(): int
    {
        return ( new Repository( \boostra\domains\ScoringEFRSB::class ) )
            ->count( [
                'status'  => ['in', [$this->scorings::STATUS_COMPLETED] ] ,
                'success' => 0,
            ] );
    }
    
    /**
     * Выгрузка данных в Excel
     *
     * @return void
     * @throws Exception
     */
    private function download(): void
    {
        ini_set( 'max_execution_time', '0' );
        ini_set( 'memory_limit', '-1' );
        
        require dirname( __DIR__ ) . '/vendor/autoload.php';
        
        $header = array_combine(
            array_column( $this->columns, 'description'),
            array_column( $this->columns, 'type')
        );
        
        $writer = new XLSXWriter();
        $writer->writeSheetHeader( 'bankruptcy_report', $header );
        
        foreach( $this->getProcessedData() as $item ){
            $writer->writeSheetRow( 'bankruptcy_report', (array) $item );
        }
        
        $filename = 'files/reports/bankruptcy_report.xlsx';
        $writer->writeToFile( $this->config->root_dir . $filename );
        header( 'Location:' . $filename );
        
        exit;
    }
}