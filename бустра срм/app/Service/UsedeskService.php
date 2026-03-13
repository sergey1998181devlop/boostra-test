<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class UsedeskService
{
    private const GET_TICKET_URI = 'ticket/';
    private const CREATE_TICKET_URI = 'create/ticket/';

    private string $apiUrl;
    private Client $client;

    public function __construct()
    {
        $this->apiUrl = config('services.usedesk.api_url');
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'PHP-MCAPI/2.0',
            ],
            'verify' => false,
        ]);
    }

    public function getTicket(string $apiToken, int $id): array
    {
        $data = [
            'api_token' => $apiToken,
            'ticket_id' => $id,
        ];

        return $this->post(self::GET_TICKET_URI, $data);
    }

    public function createTicket(string $apiToken, array $data): array
    {
        $data['api_token'] = $apiToken;

        return $this->post(self::CREATE_TICKET_URI, $data);
    }

    private function post(string $endpoint, array $data): array
    {
        try {
            $response = $this->client->post($this->apiUrl . '/' . $endpoint, [RequestOptions::FORM_PARAMS => $data]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result ?? [];
        } catch (Exception|GuzzleException $e) {
            error_log("Usedesk API Error: " . $e->getMessage());
            return [];
        }
    }
}