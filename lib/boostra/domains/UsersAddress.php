<?php

namespace boostra\domains;

use boostra\domains\abstracts\EntityObject;

/**
 * @property int $id,
 * @property string $address_index,
 * @property string $region,
 * @property string|null $region_code,
 * @property string $district,
 * @property string $city,
 * @property string $locality,
 * @property string $street,
 * @property string $housing,
 * @property string $building,
 * @property string $room,
 * @property string $region_shorttype,
 * @property string $city_shorttype,
 * @property string $street_shorttype,
 * @property string|null $fias_id
 */
class UsersAddress extends EntityObject
{
    public static function table(): string
    {
        return 'users_addresses';
    }
}