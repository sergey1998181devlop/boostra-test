<?php

namespace boostra\codeTemplates;

/**
 * Class Hydrator
 *
 * Data Transfer Object
 *
 * Should extend the specific class  as trait for usage
 * Checks if the property exists and cast it to its default type
 *
 * @version 1.0.0
 */
trait Hydrator
{
    /**
     * @param $params
     *
     * @return void
     */
    public function hydrate( $params = array() ): void
    {
        foreach ( $params as $param_name => $param ) {
            
            // if ( property_exists(static::class, $param_name) ) {
            //
            //     $type = isset( $this->$param_name )
            //         ? strtolower( gettype($this->$param_name) )
            //         : 'null';
                if( strpos( $param_name, '.' ) !== false ){
                    continue;
                }
                $this->storage[ $param_name ] = $param;
                $this->_initial_storage[ $param_name ] = $param;
				
				// Skip type casting for default undefined properties
				// if( $type === 'null' ){
				// 	continue;
				// }
				
                // settype($this->$param_name, $type);
            // }
        }
    }
}
