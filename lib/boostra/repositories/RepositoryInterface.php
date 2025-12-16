<?php

namespace boostra\repositories;

interface RepositoryInterface{
    
    /** Base actions */
    
    public function create( $data );
    public function read( $condition );
    public function save( $item, $data = null ): bool;
    public function delete( $condition ): ?int;
    
    /** Bulk base actions */
    
    /**
     * @param array $data
     *
     * @return array
     */
    // public function createBulk( array $data ): array;
    
    /**
     * @param array|object|null $condition
     *
     * @return array
     */
    // public function readBulk( $condition = null ): array;
    
    /**
     * @param array $items
     * @param array $data
     *
     * @return bool
     */
    // public function saveBulk( array $items, array $data ): bool;
    
    /**
     * @param array|object|null $condition
     *
     * @return int|null
     */
    // public function deleteBulk( $condition = null ): ?int;
    
    /** Transactions */
    
    /**
     * @return mixed
     */
    // public function beginTransaction();
    
    /**
     * @param $transaction
     *
     * @return mixed
     */
    // public function endTransaction( $transaction ): void;
}