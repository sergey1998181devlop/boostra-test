<?php

namespace App\Core\Application\Container;

class BaseContainer {

    public object $app;

    public function __construct(object $app) {
        $this->app = $app;
        //
    }

}
