<?php

namespace boostra\domains\abstracts;

interface BaseObject{
    public function hydrate( $params ): void;
}