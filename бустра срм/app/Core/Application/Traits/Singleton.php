<?php

namespace App\Core\Application\Traits;

trait Singleton {
    private static $instance;

    public static function singleton() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
