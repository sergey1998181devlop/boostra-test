<?php

namespace services\Integration\BankiRu\CheckDuplicates;

use services\Integration\BankiRu\Client\Client;
use Throwable;

final class CheckDuplicate
{
    private $url = 'check-dublicates';
    private $authToken;
    public function __construct(string $authToken)
    {
        $this->authToken = $authToken;
    }

    public function isDuplicateByPhoneNumber(string $partnerCode, string $phone, string $countryCode = '7'): bool
    {
        try {
            $headers = [
                'Authorization: Bearer ' . $this->authToken,
                'Content-Type: application/json',
            ];

            $data = [
                'partnerCode' => $partnerCode,
                'leadInfo'    => [
                    'clientData' => [
                        'phone' => [
                            'countryPrefixCode' => $countryCode,
                            'number'            => $phone,
                        ]
                    ]
                ]
            ];

            $client = new Client();
            $request = $client->request(Client::POST, $this->url, $data, $headers);

            $isD = $request['data']['dublicate'] ?? null;
            if (is_string($isD)) {
                if ($isD === 'true') {
                    return true;
                } else {
                    return  false;
                }
            }

            return boolval($request['data']['dublicate'] ?? false);

        } catch(Throwable $exception) {
            return false;
        }
    }
}