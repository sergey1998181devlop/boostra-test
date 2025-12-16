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
        $intersection = array_uintersect_assoc(
            $this->storage,
            $this->_initial_storage,
            function($a, $b){
                return 0;
            }
        );
        
        $initial = $this->_initial_storage;
        
        // Returns only difference
        return array_filter(
            $intersection,
            function( $val, $key ){
                
                // Convert to string if valueObject provide such opportunity
                $val = ! is_scalar( $val ) && ! is_null( $val ) && method_exists( get_class( $val ), '_serialize' )
                    ? $val->_serialize()
                    : $val;
                
                return array_key_exists( $key, $this->_initial_storage ) && $val != $this->_initial_storage[ $key ];
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