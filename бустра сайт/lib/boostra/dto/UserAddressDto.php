<?php

namespace boostra\dto;

use boostra\domains\Region;
use Request;

class UserAddressDto
{
    /** @var string */
    public $address_index;
    /** @var string */
    public $region;
    /** @var string|null */
    public $region_code;
    /** @var string */
    public $district;
    /** @var string */
    public $city;
    /** @var string */
    public $locality;
    /** @var string */
    public $street;
    /** @var string */
    public $building;
    /** @var string */
    public $housing;
    /** @var string */
    public $room;
    /** @var string */
    public $region_shorttype;
    /** @var string */
    public $city_shorttype;
    /** @var string */
    public $street_shorttype;
    /** @var string|null */
    public $fias_id;

    public function __construct(
        string $address_index,
        string $region,
        ?string $region_code,
        string $district,
        string $city,
        string $locality,
        string $street,
        string $housing,
        string $building,
        string $room,
        string $regionShortType,
        string $cityShorType,
        string $streetShorType,
        ?string $fiasId
    )
    {
        $this->address_index = $address_index;
        $this->region = $region;
        $this->region_code = $region_code;
        $this->district = $district;
        $this->city = $city;
        $this->locality = $locality;
        $this->street = $street;
        $this->housing = $housing;
        $this->building = $building;
        $this->room = $room;
        $this->region_shorttype = $regionShortType;
        $this->city_shorttype = $cityShorType;
        $this->street_shorttype = $streetShorType;
        $this->fias_id = $fiasId;
    }

    /**
     * @param Request $request
     * @param Region|null $region
     * @param string|null $fiasId
     * @return UserAddressDto
     */
    public static function createRegistrationAddressDtoFromRequest(Request $request, ?Region $region, ?string $fiasId = null): self
    {
        return new self(
            $request->safe_post('Regindex') ?? '',
            $request->safe_post('Regregion') ?? '',
            $region ? $region->code : null,
            $request->safe_post('Regdistrict') ?? '',
            $request->safe_post('Regcity') ?? '',
            $request->safe_post('Reglocality') ?? '',
            $request->safe_post('Regstreet') ?? '',
            $request->safe_post('Reghousing') ?? '',
            $request->safe_post('Regbuilding') ?? '',
            $request->safe_post('Regroom') ?? '',
            $request->safe_post('Regregion_shorttype') ?? '',
            $request->safe_post('Regcity_shorttype') ?? '',
            $request->safe_post('Regstreet_shorttype') ?? '',
            $fiasId ?? null
        );
    }

    /**
     * @param Request $request
     * @param Region|null $region
     * @param string|null $fiasId
     * @return UserAddressDto
     */
    public static function createFactualAddressDtoFromRequest(Request $request, ?Region $region, ?string $fiasId = null): self
    {
        return new self(
            $request->safe_post('Faktindex') ?? '',
            $request->safe_post('Faktregion') ?? '',
            $region ? $region->code : null,
            $request->safe_post('Faktdistrict') ?? '',
            $request->safe_post('Faktcity') ?? '',
            $request->safe_post('Factlocality') ?? '',
            $request->safe_post('Faktstreet') ?? '',
            $request->safe_post('Fakthousing') ?? '',
            $request->safe_post('Faktbuilding') ?? '',
            $request->safe_post('Faktroom') ?? '',
            $request->safe_post('Faktregion_shorttype') ?? '',
            $request->safe_post('Faktcity_shorttype') ?? '',
            $request->safe_post('Faktstreet_shorttype') ?? '',
            $fiasId ?? null
        );
    }

    /**
     * @param array $user
     * @param Region|null $region
     * @param string|null $fiasId
     * @return UserAddressDto
     */
    public static function createRegistrationAddressDtoFromUser(array $user, ?Region $region, ?string $fiasId = null): self
    {
        return new self(
            $user['Regindex'] ?? '',
            $user['Regregion'] ?? '',
            $region ? $region->code : null,
            $user['Regdistrict'] ?? '',
            $user['Regcity'] ?? '',
            $user['Reglocality'] ?? '',
            $user['Regstreet'] ?? '',
            $user['Reghousing'] ?? '',
            $user['Regbuilding'] ?? '',
            $user['Regroom'] ?? '',
            $user['Regregion_shorttype'] ?? '',
            $user['Regcity_shorttype'] ?? '',
            $user['Regstreet_shorttype'] ?? '',
            $fiasId ?? null
        );
    }

    /**
     * @param array $user
     * @param Region|null $region
     * @param string|null $fiasId
     * @return UserAddressDto
     */
    public static function createFactualAddressDtoFromUser(array $user, ?Region $region, ?string $fiasId = null): self
    {
        return new self(
            $user['Faktindex'] ?? '',
            $user['Faktregion'] ?? '',
            $region ? $region->code : null,
            $user['Faktdistrict'] ?? '',
            $user['Faktcity'] ?? '',
            $user['Faktlocality'] ?? '',
            $user['Faktstreet'] ?? '',
            $user['Fakthousing'] ?? '',
            $user['Faktbuilding'] ?? '',
            $user['Faktroom'] ?? '',
            $user['Faktregion_shorttype'] ?? '',
            $user['Faktcity_shorttype'] ?? '',
            $user['Faktstreet_shorttype'] ?? '',
            $fiasId ?? null
        );
    }
}