<?php

namespace boostra\domains\Transaction;

/**
 * @property int    $sector    8097
 * @property int    $id        353482919
 * @property int    $amount
 * @property int    $currency
 * @property string $pan
 * @property string $signature NjhlMDRjYTliYzE2MDA5MWQxN2JkOWQ0NDE0YzBiMTM=
 */
class RequestBody extends \boostra\domains\abstracts\ValueObject{

    public function _serialize(): string
    {
        return json_encode( $this, JSON_THROW_ON_ERROR );
    }
}