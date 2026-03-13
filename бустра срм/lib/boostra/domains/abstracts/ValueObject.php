<?php

namespace boostra\domains\abstracts;

use boostra\codeTemplates\Hydrator;

abstract class ValueObject implements BaseObject{
    
    use Hydrator;
    
    /**
     * Contains current values
     *
     * @var array
     */
    private $storage;
    
    /**
     * Contains values from DB
     *
     * @var array
     */
    private $_initial_storage;
    
    public function __construct( $params = [] )
    {
        $params
            && $this->hydrate( (array) $params );
        
        // If it's newly created object, we don't need to compare it with DB
        if( empty( $this->id ) ){
            $this->_initial_storage = [];
        }
        
        method_exists( $this, '_init')
            && $this->_init( $params );
        
        method_exists( $this, 'init')
            && $this->init();
    }
    
    public function toArray(): array
    {
        return (array) $this->storage;
    }
    
    /**
     * Get changed values
     *
     * @return array
     */
    public function getChanges(): array
    {
        // Get rid of dynamic properties
        if( method_exists( $this, '_getColumns') ){
            
            $columns = $this->_getColumns();
            $intersection = array_filter(
                $this->storage,
                static function( $val, $key ) use ( $columns ){
                    return in_array( $key, $columns, true );
                },
                ARRAY_FILTER_USE_BOTH);
        
        // Old system
        }else{
            $intersection = array_uintersect_assoc(
                $this->storage,
                $this->_initial_storage,
                static function( $a, $b ){
                    return 0;
                }
            );
        }

        foreach( $intersection as $key => &$item ){
            
            // Convert to string if valueObject provide such opportunity
            $item = is_object( $item) && method_exists( get_class( $item ), '_serialize' )
                ? $item->_serialize()
                : $item;
            
            // Delete non-scalar items
            if( ! is_scalar( $item ) ){
                unset( $intersection[ $key ] );
            }
        }
        
        // Returns only difference
        return array_filter(
            $intersection,
            function( $val, $key ){
                
                // Pass value if key is not exists in initial storage or value is not equal
                return ! array_key_exists( $key, $this->_initial_storage ) || $val !== $this->_initial_storage[ $key ];
                
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
    
    public function toObject(): object
    {
        return (object) $this->storage;
    }
    
    public function __get( $name )
    {
        return $this->storage[$name] ?? null;
    }
    
    public function __set( $name, $value )
    {
        $this->storage[$name] = $value;
    }
    
    public function __isset( $name )
    {
        return isset( $this->storage[ $name ] );
    }
}