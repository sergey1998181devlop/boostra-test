<?php

namespace boostra\repositories;

use boostra\domains\abstracts\EntityObject;
use boostra\services\Core;
use Database;
use DatabaseAccess;

class Repository implements RepositoryInterface{
    
    /**
     * Database handler
     *
     * @var Database|DatabaseAccess
     */
    private $db;
    
    /**
     * @var EntityObject|mixed
     */
    private $entity;
    
    /**
     * @var mixed|string Entity classname
     */
    private $entity_classname;
    
    public function __construct( $entity, $db = null )
    {
        $this->db               = $db ?? Core::instance()->db;
        $this->entity_classname = is_object( $entity )
            ? get_class( $entity )
            : $entity;
        $this->entity           = $this->entity_classname;
    }
    
    public function create( $data )
    {
        return new $this->entity_classname( $data );
    }
    
    public function read( $condition, $order_by = null, $order = null )
    {
        $sql_results = $this->db->select(
            $this->entity::table(),
            $condition,
            null,
            $order_by,
            $order
        );
        
        // Get the first entry to create an instance
        return $this->create( $sql_results[0] ?? [] );
    }
    
    /**
     * @param EntityObject $item
     * @param              $data
     *
     * @return bool
     */
    public function save( $item, $data = null ): bool
    {
        $data = (array) ( $data ?? $item->getChanges() );
        
        unset($data['return']);
        
        if( ! $data ){
            return true;
        }
        
        $item->getId() &&
            $this->db->query(
                $this->db->placehold(
                    'UPDATE '. $item::table() .' SET ?% WHERE id = ?',
                    $data,
                    $item->getId()
                )
            );
        
        ! $item->getId() &&
            $this->db->query(
                $this->db->placehold(
                    'INSERT INTO '. $item::table() .' SET ?%',
                    $data
                )
            );
        
        return true;
    }
    
    public function delete( $condition ): ?int
    {
    
    }
    
    /**
     * @throws \Exception
     */
    public function readBatch( $condition = [], $order_by = '', $order = 'desc', $offset = null, $amount = null, $columns = '*', $joins = null ): array
    {
        $sql_results = [];
        
        // Using query builder
        if( $this->db instanceof DatabaseAccess ){
            $sql_results = $this->db
                ->from( $this->entity::table() )
                ->columns( $columns )
                ->orderBy( $order_by, $order )
                ->limit( $amount, $offset )
                ->where( $condition )
                ->join( $joins ?? $this->entity::instantRelations() )
                ->many();
            
        // using direct SQL string builder
        }elseif( $this->db instanceof Database ){
            $sql_results = $this->db->select(
                $this->entity::table(),
                $condition,
                method_exists( $this->entity, 'instantRelations' )
                    ? $this->entity::instantRelations()
                    : null,
                $order_by,
                $order,
                $offset,
                $amount
            );
        }
        
        // Get the first entry to create an instance
        $results = [];
        foreach( $sql_results as $sql_result ){
            $results[] = $this->create( $sql_result );
        }
        
        return $results;
    }

    public function count( $condition ): int
    {
        return $this->db
            ->from( $this->entity::table() )
            ->where( $condition )
            ->count();
    }
}