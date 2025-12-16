<?php

namespace boostra\domains\Scoring;

/**
 * @property int    $sector    8097
 * @property int    $id        353482919
 * @property int    $amount
 * @property int    $currency
 * @property string $pan
 * @property string $signature NjhlMDRjYTliYzE2MDA5MWQxN2JkOWQ0NDE0YzBiMTM=
 */
class ResponseBody extends \boostra\domains\abstracts\ValueObject
{
    public function _serialize(): string
    {
        return serialize( (object) $this );
    }
}