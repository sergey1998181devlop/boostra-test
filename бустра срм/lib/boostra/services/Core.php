<?php

namespace boostra\services;

use boostra\codeTemplates\Singleton;
use Simpla;

class Core extends Simpla{
    
    use Singleton;
    
    public function init()
    {
        self::instance();
    }
}