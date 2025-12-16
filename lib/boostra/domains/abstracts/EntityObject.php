<?php

namespace boostra\domains\abstracts;

use boostra\services\Core;
use boostra\repositories\Repository;

abstract class EntityObject extends ValueObject{
    
    abstract public static function table(): string;
    
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return void
     */
    public function save(): void
    {
        ( new Repository( get_class( $this ) ) )
            ->save( $this );
    }

    public static function instantRelations(): array
    {
        return [];
    }
    
    /**
     * @throws \Exception
     */
    public function _init( $params )
    {
        /** Set dynamic object properties */
        if( method_exists( $this, 'relations') ){
            $this->_set_relation();
        }
        
        /** Set static sub object properties */
        foreach( $params as $key => $property ){
            if( strpos( $key, '.' ) !== false ){
                $exploded_key = explode( '.', $key );
                $new_key      = $exploded_key[0];
                $new_subkey      = $exploded_key[1];
                $this->$new_key = $this->$new_key ?? (object)[];
                $this->$new_key->$new_subkey = $property;
                unset( $this->$key );
            }
        }
    }
    
    private function _set_relation()
    {
        foreach( $this->relations() as $property_name => $relation ){
            $relation['type'] = $relation['type'] ?? 'single';
            
            if( ! $relation['classname'] ){
                throw new \Exception( "No class name provided for relation $property_name in " . static::class );
            }
            
            $repo = new Repository( $relation['classname'] );
            
            // Skip empty conditions. Because it's braking relation anyway
            foreach( $relation['condition'] as $condition_item ){
                if( is_null( $condition_item ) ){
                    $this->$property_name = null;
                    continue( 2 );
                }
            }
            
            $this->$property_name = $relation['type'] === 'single'
                ? $repo->read( $relation['condition'] )
                : $repo->readBatch( $relation['condition'] );
        }
    }
}