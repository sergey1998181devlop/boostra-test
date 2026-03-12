<?php

namespace boostra\services;

use boostra\domains\UsersAddress;
use boostra\dto\UserAddressDto;
use Request;
use stdClass;

class UsersAddressService extends Core
{
    /**
     * @param stdClass $user
     * @return void
     */
    public function addUserAddressesToUser(stdClass $user): void
    {
        $user->Regregion_code = null;
        $user->Faktregion_code = null;

        if (!empty($user->registration_address_id)) {
            $regUserAddress = $this->getUserAddress($user->registration_address_id);

            if ($regUserAddress !== null) {
                $this->addRegistrationAddressToUser($user, $regUserAddress);
            }
        }

        if (!empty($user->factual_address_id)) {
            $faktUserAddress = $this->getUserAddress($user->factual_address_id);

            if ($faktUserAddress !== null) {
                $this->addFactualAddressToUser($user, $faktUserAddress);
            }
        }
    }

    /**
     * @param int $addressId
     * @return UsersAddress|null
     */
    public function getUserAddress(int $addressId): ?UsersAddress
    {
        $query = $this->db->placehold(
            sprintf('SELECT * FROM %s WHERE id = ?', UsersAddress::table()),
            $addressId
        );

        $this->db->query($query);
        $userAddress = $this->db->result();

        if (empty($userAddress)) {
            return null;
        }

        return (new UsersAddress($userAddress));
    }

    /**
     * @param stdClass $user
     * @param UsersAddress $userAddress
     * @return void
     */
    public function addRegistrationAddressToUser(stdClass $user, UsersAddress $userAddress): void
    {
        $user->Regindex = $userAddress->address_index;
        $user->Regregion = $userAddress->region;
        $user->Regregion_code = $userAddress->region_code;
        $user->Regdistrict = $userAddress->district;
        $user->Regcity = $userAddress->city;
        $user->Reglocality = $userAddress->locality;
        $user->Regstreet = $userAddress->street;
        $user->Regbuilding = $userAddress->building;
        $user->Reghousing = $userAddress->housing;
        $user->Regroom = $userAddress->room;
        $user->Regregion_shorttype = $userAddress->region_shorttype;
        $user->Regcity_shorttype = $userAddress->city_shorttype;
        $user->Regstreet_shorttype = $userAddress->street_shorttype;
        $user->Regfias_id = $userAddress->fias_id;
    }

    /**
     * @param stdClass $user
     * @param UsersAddress|stdClass $userAddress
     * @return void
     */
    public function addFactualAddressToUser(stdClass $user, $userAddress): void
    {
        $user->Faktindex = $userAddress->address_index;
        $user->Faktregion = $userAddress->region;
        $user->Faktregion_code = $userAddress->region_code;
        $user->Faktdistrict = $userAddress->district;
        $user->Faktcity = $userAddress->city;
        $user->Faktlocality = $userAddress->locality;
        $user->Faktstreet = $userAddress->street;
        $user->Faktbuilding = $userAddress->building;
        $user->Fakthousing = $userAddress->housing;
        $user->Faktroom = $userAddress->room;
        $user->Faktregion_shorttype = $userAddress->region_shorttype;
        $user->Faktcity_shorttype = $userAddress->city_shorttype;
        $user->Faktstreet_shorttype = $userAddress->street_shorttype;
        $user->Faktfias_id = $userAddress->fias_id;
    }

    /**
     * @param UsersAddress $userAddress
     * @return int
     */
    public function saveNewAddress(UsersAddress $userAddress): int
    {
        $userAddress = $userAddress->toArray();
        unset($userAddress['id']);

        $query = $this->db->placehold(
            sprintf('INSERT INTO %s SET ?%%', UsersAddress::table()), // экранирование второго %
            $userAddress
        );

        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * @param int $userAddressId
     * @param UsersAddress $userAddress
     * @return void
     */
    public function updateAddress(int $userAddressId, UsersAddress $userAddress): void
    {
        $userAddress = $userAddress->toArray();

        $query = $this->db->placehold(
            sprintf('UPDATE %s SET ?%% WHERE id=?', UsersAddress::table()),
            $userAddress,
            $userAddressId
        );

        $this->db->query($query);
    }

    /**
     * @param UserAddressDto|UsersAddress $userAddress
     * @return array
     */
    private function getDadataAddress($userAddress): array
    {
        $queryParts = [];

        if (!empty($userAddress->region)) {
            $queryParts[] = $userAddress->region . ' ' . $userAddress->region_shorttype;
        }

        if (!empty($userAddress->locality)) {
            if (!empty($userAddress->district)) {
                $queryParts[] = $userAddress->district;
            }
            $queryParts[] = $userAddress->city_shorttype . ' ' . $userAddress->locality;
        } else {
            if (!empty($userAddress->city)) {
                $queryParts[] = $userAddress->city_shorttype . ' ' . $userAddress->city;
            }
        }

        $queryRegion = implode(', ', $queryParts);

        if (!empty($userAddress->street)) {
            $queryParts[] = $userAddress->street_shorttype . ' ' . $userAddress->street;
        }

        if (!empty($userAddress->housing)) {
            $queryParts[] = $userAddress->housing;
        }

        if (!empty($userAddress->building)) {
            $queryParts[] = $userAddress->building;
        }

        if (!empty($userAddress->room)) {
            $queryParts[] = $userAddress->room;
        }

        $query  = implode(', ', $queryParts);
        $dadata = (new DadataService())->getDadataAddress($query);
        if(empty($dadata)) {
            $dadata = (new DadataService())->getDadataAddress($queryRegion);
        }
        return $dadata;
    }

    /**
     * @param string $addressString
     * @return array
     */
    public function getAddressFromString(string $addressString): array
    {
        return (new DadataService())->getDadataAddress($addressString);
    }

    /**
     * @param int $userAddressId
     * @param Request $request
     * @return void
     */
    public function updateRegistrationAddress(int $userAddressId, Request $request): void
    {
        $oldUserAddress = $this->getUserAddress($userAddressId);

        if ($oldUserAddress === null) {
            return;
        }

        $region = (new RegionService())->getRegionByName($request->safe_post('Regregion'));

        $userAddressDto = UserAddressDto::createRegistrationAddressDtoFromRequest($request, $region);

        $userAddress = $this->getUserAddressFromUserAddressDto($userAddressDto);

        $this->updateAddress($userAddressId, $userAddress);
    }

    /**
     * @param int $userAddressId
     * @param Request $request
     * @return void
     */
    public function updateFactualAddress(int $userAddressId, Request $request): void
    {
        $oldUserAddress = $this->getUserAddress($userAddressId);

        if ($oldUserAddress === null) {
            return;
        }

        $region = (new RegionService())->getRegionByName($request->safe_post('Faktregion'));

        $userAddressDto = UserAddressDto::createFactualAddressDtoFromRequest($request, $region);

        $userAddress = $this->getUserAddressFromUserAddressDto($userAddressDto);

        $this->updateAddress($userAddressId, $userAddress);
    }

    /**
     * @param UsersAddress $userAddress
     * @param array $dadataAddress
     * @return void
     */
    private function fillUserAddressWithDadataAddress(UsersAddress $userAddress, array $dadataAddress): void
    {
        if (empty($userAddress->fias_id)) {
            $userAddress->fias_id = $dadataAddress['fias_id'] ?? null;
        }

        if (empty($userAddress->address_index)) {
            $userAddress->address_index = $dadataAddress['postal_code'] ?? '';
        }

        if (empty($userAddress->district)) {
            $userAddress->district = $dadataAddress['area'] ?? '';
        }

        if (empty($userAddress->locality)) {
            $userAddress->locality = $dadataAddress['settlement'] ?? '';
        }

        if (empty($userAddress->region_shorttype)) {
            $userAddress->region_shorttype = $dadataAddress['region_type'] ?? '';
        }

        if (empty($userAddress->region_code)) {
            // у этих регионов код по КЛАДР не совпадет с кодами ГИБДД, которые используются у нас
            $conversions = [
                '94' => '81',
                '93' => '80',
                '20' => '95',
                '95' => '84',
                '91' => '82',
            ];
            $region_code = substr($dadataAddress['region_kladr_id'] ?? '', 0, 2);
            $userAddress->region_code = $conversions[$region_code] ?? $region_code;
        }

        if (empty($userAddress->city_shorttype)) {
            if (!empty($dadataAddress['settlement_type'])) {
                $userAddress->city_shorttype = $dadataAddress['settlement_type'];
            } else {
                $userAddress->city_shorttype = $dadataAddress['city_type'] ?? '';
            }
        }

        if (empty($userAddress->street_shorttype)) {
            $userAddress->street_shorttype = $dadataAddress['street_type'] ?? '';
        }
    }

    /**
     * @param Request $request
     * @return UsersAddress
     */
    public function getRegistrationAddress(Request $request): UsersAddress
    {
        $region = (new RegionService())->getRegionByName($request->safe_post('Regregion'));
        $userAddressDto = UserAddressDto::createRegistrationAddressDtoFromRequest($request, $region);
        return $this->getUserAddressFromUserAddressDto($userAddressDto);
    }

    /**
     * @param Request $request
     * @return UsersAddress
     */
    public function getFactualAddress(Request $request): UsersAddress
    {
        $region = (new RegionService())->getRegionByName($request->safe_post('Faktregion'));
        $userAddressDto = UserAddressDto::createFactualAddressDtoFromRequest($request, $region);
        return $this->getUserAddressFromUserAddressDto($userAddressDto);
    }

    /**
     * @param UserAddressDto $userAddressDto
     * @return UsersAddress
     */
    private function getUserAddressFromUserAddressDto(UserAddressDto $userAddressDto): UsersAddress
    {
        $userAddress = new UsersAddress($userAddressDto);

        $dadataAddress = $this->getDadataAddress($userAddressDto);
        $this->fillUserAddressWithDadataAddress($userAddress, $dadataAddress);

        return $userAddress;
    }

    /**
     * @param array $user
     * @return UsersAddress
     */
    public function getRegistrationAddressFromUser(array $user): UsersAddress
    {
        $region = (new RegionService())->getRegionByName($user['Regregion']);
        if(!$region) {
            $region = (new RegionService())->getRegionByCode($user['Regregion']);
        }
        $userAddressDto = UserAddressDto::createRegistrationAddressDtoFromUser($user, $region);
        return $this->getUserAddressFromUserAddressDto($userAddressDto);
    }

    /**
     * @param array $user
     * @return UsersAddress
     */
    public function getFactualAddressFromUser(array $user): UsersAddress
    {
        $region = (new RegionService())->getRegionByName($user['Faktregion']);
        $userAddressDto = UserAddressDto::createFactualAddressDtoFromUser($user, $region);
        return $this->getUserAddressFromUserAddressDto($userAddressDto);
    }

    /**
     * Запрашивает и сохраняет ОКТМО и ОКАТО
     * @param int|string $user_id
     * @param UserAddressDto|UsersAddress $userAddress
     * @return void
     */
    public function saveOktmo($user_id, $userAddress)
    {
        $dadataAddress = $this->getDadataAddress($userAddress);

        if (!empty($dadataAddress['oktmo'])) {
            $this->user_data->set($user_id, 'oktmo', $dadataAddress['oktmo']);
        }

        if (!empty($dadataAddress['okato'])) {
            $this->user_data->set($user_id, 'okato', $dadataAddress['okato']);
        }
    }
}
