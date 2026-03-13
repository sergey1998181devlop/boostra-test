<?php

namespace boostra\services;

use stdClass;

class DadataService extends Core
{
    private string $token;
    private const DADATA_API_URL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/';
    private const DADATA_LOCATION_IP_URL = 'http://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address';
    private RegionService $regionService;

    public function __construct()
    {
        parent::__construct();

        $this->token = $this->settings->getApiKeys('dadata')['api_key'];
        $this->regionService = new RegionService();
    }

    /**
     * @param string $query
     * @return array
     */
    public function getDadataAddress(string $query): array
    {
        $request = [
            "query" => $query,
            "count" => 1,
        ];

        $dadataService = new DadataService();
        $dadataAddress = $dadataService->suggest('address', $request);

        if (empty($dadataAddress)) {
            return [];
        }

        $dadataAddress = json_decode($dadataAddress, true);

        if (empty($dadataAddress['suggestions'])) {
            return [];
        }

        return $dadataAddress['suggestions'][0]['data'];
    }

    /**
     * @param string $type
     * @param array|stdClass $fields
     * @return bool|string
     */
    public function suggest(string $type, $fields)
    {
        $ch = curl_init(self::DADATA_API_URL . $type);

        if (!$ch) {
            return false;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token ' . $this->token
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    /**
     * @param string|null $regionName
     * @return string|null
     */
    public function getRegionCodeByRegionName(?string $regionName): ?string
    {
        if ($regionName === null) {
            return null;
        }

        $dadataAddress = $this->suggest("address", ['query' => $regionName, 'count' => 1]);

        if (empty($dadataAddress)) {
            return null;
        }

        $dadataAddress = json_decode($dadataAddress, true);

        if (empty($dadataAddress['suggestions']) || empty($dadataAddress['suggestions'][0]['data']['region'])) {
            return null;
        }

        $dadataRegionName = $dadataAddress['suggestions'][0]['data']['region'];

        $region = $this->regionService->getRegionByName($dadataRegionName);

        if ($region === null) {
            return null;
        }

        return $region->code;
    }

    /**
     * Получить местоположение по ip
     *
     * @param string $ip
     * @return bool|string
     */
    public function getLocationByIp(string $ip)
    {
        $data = [
            'ip' => $ip
        ];

        $ch = curl_init(self::DADATA_LOCATION_IP_URL);

        if (!$ch) {
            return false;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token ' . $this->token
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }
}
