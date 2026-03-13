<?php

/**
 * Класс для доступа к базе данных
 *
 * @copyright 	2023 Roman Safronov
 * @author 		Roman Safronov
 *
 */

use boostra\domains\User;

require_once('Simpla.php');

class DatabaseAccess extends Database
{
    private $table = '';
    private $columns = '*';
    private $conditions = [];
    private $joins = '';
    private $count = false;
    protected $offset = 0;
    protected $amount = 1000;
    private $limit = '';
    private $order_by = '';
    private $where = '';
    
    private $operators = [
        '=',
        '!=',
        '>',
        '<',
        '>=',
        '<=',
        'in',
        'like',
    ];
    
    /**
     * Устанавливает таблицу из которой будет выборка
     *
     * @param string $table
     *
     * @return $this
     */
    public function from( string $table ): self
    {
        $this->table = $table;
        
        return $this;
    }
    
    /**
     *  Собирает строку запрашиваемых колонок и помещает её в $this->columns
     *
     * @param array|string $columns
     *
     * @return $this
     */
    public function columns( $columns ): self
    {
        $columns = ! is_array( $columns )
            ? explode( ',', $columns )
            : $columns;
        
        array_walk( $columns, static function( &$item, $key, $table_name ){ $item = $table_name . '.' . trim( $item ); }, $this->table );
        
        $this->columns = implode( ',', $columns );
        
        return $this;
    }
    
    /**
     * Обрабатывает зависимости класса
     *
     * Меняет $this->columns, дописывает в него поля от "отношений". Например: ТАБЛИЦА_ОТНОШЕНИЙ.ПОЛЕ AS "ТАБЛИЦА_ОТНОШЕНИЙ.ПОЛЕ"
     *  для того что бы в результатах можно было понять какое поле к чему относиться, не смотря на дублирование имен полей
     *
     * Собирает строку JOIN и помещает её в $this->joins. Например:
     *  'INNER JOIN s_users ON s_orders.user_id = s_users.id INNER JOIN s_contracts ON s_orders.contract_id = s_contracts.id INNER JOIN s_scorings ON s_orders.id = s_scorings.order_id
     *
     * Отношений может быть больше одного, они могут быть разных типов. inner, outer, left, full outer и так далее
     *
     * @param array $joins
     *                    'user' => [
     *                          'classname' => User::class,
     *                          'condition' => [ 'user_id' => 'id', ],
     *                          'type'      => 'inner|outer|left|full outer|...'
     *                          'columns'   => [ 'inn' ],
     *                    ],
     *
     * @return $this
     */
    public function join( array $joins ): self
    {
        foreach( $joins as $relation => &$join ){
            
            $join_table    = $join['classname']::table();
            $on            = "$this->table." . key( $join['condition'] ) . " = $join_table." . current( $join['condition'] );
            $type          = $join['type'] ?? 'INNER';
            $join_columns  = $join['columns'] ?? $join['classname']::_getColumns();
            // $where        .= $join['condition']
            //     ? ' AND ' . implode(
            //         ' AND ',
            //         array_map(
            //             static function( $column ) use ( $join_table, $conditions ){
            //                     return "$join_table.$column = ?";
            //                 },
            //             $join['condition']
            //         )
            //     )
            //     : '';
            $this->columns .= ', ' . implode(
                    ', ',
                    array_map(
                        function( $column ) use ( $join_table, $relation ){
                            return "$join_table.$column AS " . $this->wrap( "$relation.$column" );
                        },
                        $join_columns
                    )
                );
            $join = "$type JOIN $join_table ON $on";
        } unset( $join );
        
        $this->joins = implode( ' ', $joins );
        
        return $this;
    }
    
    /**
     * Разбирает входящие условия и формируюет из них строку условий для SQL-запроса
     * Помещает её в $this->where
     *
     * Поддерживаются операторы:
     * - '='
     * - '!='
     * - '>'
     * - '<'
     * - '>='
     * - '<='
     * - 'in'
     * - 'like'
     *
     * Поддерживаются функции колонки функции. Например:
     *
     * [ 'datediff( NOW(), date_added)' => [ '>', 30, 'function' ] ]
     *
     * @throws Exception
     */
    public function where( array $conditions = [] ): self
    {
        $where = [];
        foreach( $conditions as $column => &$condition ){
            
            // Make condition standard
            $condition = is_array( $condition )
                ? $condition
                : ['=', $condition];
            
            $operator    = strtolower( $condition[0] );
            $operand     = $condition[1];
            $column_type = $condition[2] ?? 'column';
            
            if( ! in_array( $operator, $this->operators) ){
                throw new Exception('Unsupported operator');
            }
            
            switch( $operator ){
                case 'in':
                    $operand = array_map(
                        function( $item ){
                            return $this->wrap( $item );
                        },
                        $operand
                    );
                    $operands_string = '(' . implode( ',', $operand ) . ')';
                    $where[]         = "$this->table.$column IN $operands_string";
                    break;
                default:
                    $this->conditions[] = $operand;
                    $where[]            = ( $column_type === 'column' ? "$this->table." : '' ) ."$column $operator ?";
            }
        } unset( $condition );
        
        $this->where = $where
            ? 'WHERE ' . implode( ' AND ', $where )
            : '';
        
        return $this;
    }
    
    /**
     * Собирает строку ORDER BY и помещает её в $this->order_by
     *
     * @param string|null $column
     * @param string      $order
     *
     * @return $this
     * @throws Exception
     */
    public function orderBy( string $column = null, string $order = 'DESC' ): self
    {
        
        if( $column ){
            
            // Защита от SQL-инъекции. Смотри todo ниже.
            if( ! preg_match( '@^(|[a-zA-Z0-9_-]+)$@', $column ) ){
                throw new \Exception( 'Не корректное имя колонки ' . $column );
            }
            
            // Защита от SQL-инъекции. Смотри todo ниже.
            $order = strtoupper( $order );
            if( ! in_array( $order, ['DESC', 'ASC'], true ) ){
                throw new \Exception( 'Не корректное значение сортировки ' . $order );
            }
            
            // @todo Не через плейсхолдер потому что система не умеет вставлять параметр в конструкцию ORDER BY без кавычек и в итоге сортировка не работает
            $this->order_by = " ORDER BY $column $order ";
        }
        
        return $this;
    }
    
    /**
     * Собирает строку LIMIT и помещает её в $this->limit
     *
     * @param $amount
     * @param $offset
     *
     * @return $this
     */
    public function limit( $amount = 1000, $offset = 0 ): self
    {
        $this->offset = $offset ?? $this->offset;
        $this->amount = $amount ?? $this->amount;
        
        $this->limit  = $this->offset || $this->amount ? 'LIMIT ' : '';
        $this->limit .= $this->offset ?? '';
        $this->limit .= $this->amount ? ", $this->amount" : '';
        
        return $this;
    }
    
    /**
     * Выполняет выборку одного элемента, устанавливая limit в 1
     *
     * @throws Exception
     */
    public function one()
    {
        $this->count = false;
        
        return $this
            ->limit(1)
            ->runCompiledQuery();
    }
    
    /**
     * Выполняет выборку нескольких элементов
     *
     * @throws Exception
     */
    public function many()
    {
        $this->count = false;
        
        return $this->runCompiledQuery();
    }
    
    /**
     * Выполняет выборку подсчет элементов для составленного запроса
     *
     * @throws Exception
     */
    public function count(): int
    {
        $this->count = true;
        $this->limit = '';
        $this->columns = 'COUNT(*) as total';
        
        $bd_result = $this->runCompiledQuery();
        
        return (int) $bd_result[0]->total;
    }
    
    /**
     * Собирает и возвращает запрос из частей
     *
     * @throws Exception
     */
    public function getCompiledQuery(): string
    {
        if( ! $this->table ){
            throw new Exception('No table set for request');
        }
        
        return $this->db->placehold(
            "SELECT $this->columns FROM $this->table $this->joins $this->where $this->order_by $this->limit",
            ...(array_values( $this->conditions ) )
        );
    }
    
    /**
     * Собирает и выполняет запрос, получает результаты
     * @throws Exception
     */
    private function runCompiledQuery()
    {
        $query = $this->getCompiledQuery();
        
        $this->db->query( $query );
        
        $this->cleanParameters();
        
        return $this->db->results();
    }
    
    /**
     * Очищает параметры запроса
     *
     * @return void
     */
    public function cleanParameters()
    {
        $this->table      = '';
        $this->columns    = '*';
        $this->conditions = [];
        $this->joins      = '';
        $this->count      = false;
        $this->offset     = 0;
        $this->amount     = 1000;
        $this->limit      = '';
        $this->order_by   = '';
        $this->where      = '';
    }
    
    /**
     * Оборачивает строку в необходимые символы
     *
     * @param $string
     * @param $char
     *
     * @return string
     */
    private function wrap( $string, $char = '"' ): string
    {
        return str_pad( $string, strlen( $string ) + 2, $char, STR_PAD_BOTH );
    }
}

