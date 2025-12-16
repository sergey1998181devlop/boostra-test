<?php

spl_autoload_register(

    static function( $class_name ) {

        $e = explode( '\\', $class_name );
        $class_name = $e[1];
        $class_name = str_replace( '\\', '/', $class_name );
        $dir = __DIR__ . '/';

        $file = $dir . $class_name . '.php';

        if( file_exists( $file ) ){
            require_once $file;
        }
    }
);