<?php

namespace boostra\services;

abstract class BaseService
{    
    public function __construct( ...$params )
    {
        method_exists( static::class, 'init')
            && static::init( ...$params );
    }
    
    /**
     * Converts entity name to classname
     * Example:
     *  - entity name: 'user-activity'     -> classname: '\lib\boostra\domains\UserActivity'
     *  - entity name: 'user'              -> classname: '\lib\boostra\domains\user\User'
     *  - entity name: 'user-activity-log' -> classname: '\lib\boostra\domains\userActivityLog\UserActivityLog'
     *
     * @param string  $entity
     * @param ?string $additional_namespace
     *
     * @return string
     * @throws \Exception
     */
    public static function convertEntityNameToClassname( string $entity, string $additional_namespace = null )
    {
        $class_namespace = '\boostra\domains\\' . ( $additional_namespace ? $additional_namespace . '\\' : '' );
        $classname       = self::convertToCamelCase( $entity );
        $full_classname  = $class_namespace . $classname;
        $full_classname  = class_exists( $full_classname )
            ? $full_classname
            : $class_namespace . self::convertToCamelCase( $entity, true ) . '\\' . $classname;
        
        if( ! class_exists( $full_classname ) ){
            throw new \Exception( "Entity class {$full_classname} not found" );
        }
        
        return $full_classname;
    }
    
    public static function convertToCamelCase( string $string, $first_char_to_lower = false ): string
    {
        $string = str_replace( ['-','_'], ' ', $string );
        $string = ucwords( $string );
        $string = str_replace( ' ', '', $string );
        
        if( $first_char_to_lower ){
            $string = lcfirst( $string );
        }
        
        return $string;
    }}