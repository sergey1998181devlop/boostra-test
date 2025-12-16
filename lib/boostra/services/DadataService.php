<?php

namespace boostra\services;

class DadataService extends Core
{
    private $token;
    private const DADATA_API_URL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/';

    public function __construct()
    {
        parent::__construct();

        $this->token = $this->settings->apikeys['dadata']['api_key'];
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
     * @param string $subdivision_code
     * @return array
     */
    public function getPassportIssued(string $subdivision_code): array
    {
        $request = [
            "query" => $subdivision_code,
            "count" => 1,
        ];

        $result = $this->suggest('fms_unit', $request);

        if (empty($result)) {
            return [];
        }

        $result = json_decode($result, true);

        if (empty($result['suggestions'])) {
            return [];
        }

        return $result['suggestions'][0]['data'];
    }

    /**
     * @param string $type
     * @param array $fields
     * @return bool|string
     */
    public function suggest(string $type, array $fields)
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
        //Создаем переменную для дебагера
        $error = curl_error($ch);

        curl_close($ch);

        return $result;
    }
}
