<?php

/**
 * Autoloader
 */
spl_autoload_register(
    
    static function( $class_name ){
        
        $class_name = str_replace( '\\', '/', $class_name );
        $dir = strpos( $class_name, 'boostra' ) === false
            ? '../api/'
            : __DIR__ . '/';
        $file = $dir . $class_name . '.php';
        
        if( file_exists( $file ) ){
            require_once $file;
        }
    }
);