<?php

namespace boostra\domains;

use boostra\domains\abstracts\EntityObject;

/**
 * @property  int $id
 * @property  string $code
 * @property  string $name
 * @property  string|null $district
 */
class Region extends EntityObject
{
    public static function table(): string
    {
        return 'regions';
    }
}