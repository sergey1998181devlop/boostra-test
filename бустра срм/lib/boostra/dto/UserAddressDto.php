<?php

namespace boostra\dto;

use boostra\domains\Region;
use Request;
use stdClass;

class UserAddressDto
{
    public string $address_index;
    public string $region;
    public ?string $region_code;
    public string $district;
    public string $city;
    public string $locality;
    public string $street;
    public string $building;
    public string $housing;
    public string $room;
    public string $region_shorttype;
    public string $city_shorttype;
    public string $street_shorttype;
    public ?string $fias_id;

    public function __construct(
        string  $address_index,
        string  $region,
        ?string $region_code,
        string  $district,
        string  $city,
        string  $locality,
        string  $street,
        string  $housing,
        string  $building,
        string  $room,
        string  $regionShortType,
        string  $cityShorType,
        string  $streetShorType,
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
     * @param stdClass $user
     * @return UserAddressDto
     */
    public static function createFactualAddressDtoFromUser(stdClass $user): self
    {
        return new self(
            $user->Faktindex ?? '',
            $user->Faktregion ?? '',
            null ?? '',
            $user->Faktdistrict ?? '',
            $user->Faktcity ?? '',
            $user->Factlocality ?? '',
            $user->Faktstreet ?? '',
            $user->Fakthousing ?? '',
            $user->Faktbuilding ?? '',
            $user->Faktroom ?? '',
            $user->Faktregion_shorttype ?? '',
            $user->Faktcity_shorttype ?? '',
            $user->Faktstreet_shorttype ?? '',
            null
        );
    }
}
