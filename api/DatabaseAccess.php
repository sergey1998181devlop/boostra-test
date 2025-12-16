<?php

/**
 * Класс для доступа к базе данных
 *
 * @copyright 	2013 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */

use boostra\domains\User;

require_once('Simpla.php');

class DatabaseAccess extends Database
{
    private $table;
    private $columns = '*';
    private $conditions;
    private $joins;
    private $count = false;
    protected $offset = 0;
    protected $amount = 1000;
    private $limit;
    private $order_by;
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
    
    public function from( string $table ): self
    {
        $this->table = $table;
        
        return $this;
    }

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
            
            $operator = strtolower( $condition[0] );
            $operand  = $condition[1];
            
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
    
    public function orderBy( string $column, string $order = 'DESC' ): self
    {
        if( $column ){
            // @todo Не через плейсхолдер потому что система не умеет вставлять параметр в конструкцию ORDER BY без кавычек и в итоге сортировка не работает
            $order          = $order === 'desc' ? 'DESC' : 'ASC';
            $this->order_by = " ORDER BY $column $order ";
        }
        
        return $this;
    }
    
    public function limit( $amount = 1000, $offset = 0 ): self
    {
        $this->offset = $offset ?? $this->offset;
        $this->amount = $amount ?? $this->amount;
        $this->limit = 'LIMIT ' . ( $this->offset ? "$this->offset ," : '' ) . ( $this->amount ?: '' );
        
        return $this;
    }
    
    public function one()
    {
        return $this
            ->limit(1)
            ->runCompiledQuery();
    }

    public function many()
    {
        return $this->runCompiledQuery();
    }

    public function count()
    {
        $this->count = true;
        $this->limit = '';
        
        return $this
            ->columns('COUNT(*) as total')
            ->runCompiledQuery();
    }
    
    public function getCompiledQuery()
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
     * @throws Exception
     */
    private function runCompiledQuery()
    {
        if( ! $this->table ){
            throw new Exception('No table set for request');
        }
        
        $sql = $this->getCompiledQuery();
        
        $this->db->query(
            $this->db->placehold(
                "SELECT $this->columns FROM $this->table $this->joins $this->where $this->order_by $this->limit",
                ...(array_values( $this->conditions ) )
            )
        );
        
        // $this->cleanParameters();
        
        return ! $this->count
            ? $this->db->results()
            : (int) $this->db->result( 'total');
    }
    
    private function cleanParameters()
    {
        $this->table      = '';
        $this->columns    = '*';
        $this->conditions = '';
        $this->joins      = '';
        $this->count      = false;
        $this->offset     = 0;
        $this->amount     = 1000;
        $this->limit      = '';
        $this->order_by   = '';
        $this->where      = '';
    }
    
    private function wrap( $string, $char = '"' ): string
    {
        return str_pad( $string, strlen( $string ) + 2, $char, STR_PAD_BOTH );
    }
}

