<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class ObuchatService
{
    private string $apiUrl;
    private string $webhookIncomingRecordRating;
    private string $webhookOutgoingRecordRating;
    private Client $client;

    public function __construct()
    {
        $this->apiUrl = config('services.obuchat.api_url');
        $this->webhookIncomingRecordRating = config('services.obuchat.webhook_incoming_record_rating');
        $this->webhookOutgoingRecordRating = config('services.obuchat.webhook_outgoing_record_rating');
        $this->client = new Client([
            'timeout' => 120,
            'connect_timeout' => 5,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function sendIncomingRecordForRating(array $data): bool
    {
        return $this->post($this->webhookIncomingRecordRating, $data);
    }

    public function sendOutgoingRecordForRating(array $data): bool
    {
        return $this->post($this->webhookOutgoingRecordRating, $data);
    }

    private function post(string $endpoint, array $data): bool
    {
        try {
            logger('obuchat')->info('Отправка данных в Obuchat', [
                'endpoint' => $endpoint,
                'data' => $data,
            ]);

            $response = $this->client->post($this->apiUrl . '/' . $endpoint, [RequestOptions::JSON => $data]);

            return $response->getStatusCode() === 200;
        } catch (Exception|GuzzleException $e) {
            logger('obuchat')->error('Ошибка отправки данных в Obuchat', [
                'endpoint' => $endpoint,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            error_log("Obuchat API Error: " . $e->getMessage());
            return false;
        }
    }
}