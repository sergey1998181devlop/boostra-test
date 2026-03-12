<?php

namespace boostra\domains;

/**
 * @property int    $id
 * @property string $number
 */
class Loan extends \boostra\domains\abstracts\EntityObject{
    
    public static function table(): string
    {
        return 's_contracts';
    }
}