<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NotifyApi extends Simpla
{
    const API_URL = 'https://nc.mkkcollection.ru/api/';
    const API_URL_DEV = 'https://nc.dev.mkkcollection.ru/api/';

    private Client $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => $this->config->is_dev ? self::API_URL_DEV : self::API_URL,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getToken(array $credentials): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->config->token_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($credentials));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = json_decode(curl_exec($ch), true);

        if (curl_errno($ch)) {
            $this->logging(__METHOD__, $this->config->token_url, $credentials, curl_error($ch), 'api-error.log');
        }

        curl_close($ch);

        return isset($response['token']) ? $response['token'] : '';
    }

    public function contactMe(array $credentials)
    {
        $ch = curl_init();

        $token = $this->getToken($credentials);

        // Настройка cURL
        curl_setopt($ch, CURLOPT_URL, $this->config->contact_me_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);

        // Выполнение cURL-сеанса
        $response = json_decode(curl_exec($ch), true);

        // Если произошла ошибка
        if (curl_errno($ch)) {
            $this->logging(__METHOD__, $this->config->contact_me_url, $credentials, curl_error($ch), 'api-error.log');
        }

        // Завершение сеанса и освобождение ресурсов
        curl_close($ch);

        return array_merge($response, ['token' => $token]);
    }

    public function subscribeToWebNotification(array $credentials)
    {
        $ch = curl_init();

        $token = $this->getToken(array_filter($credentials, function ($key) {
            return $key !== 'subscription';
        }, ARRAY_FILTER_USE_KEY));

        $subscription = array_filter($credentials, function ($key) {
            return $key === 'subscription';
        }, ARRAY_FILTER_USE_KEY);

        curl_setopt($ch, CURLOPT_URL, $this->config->web_notification_subscribe_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($subscription));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);

        // Выполнение cURL-сеанса
        $response = json_decode(curl_exec($ch), true);

        // Если произошла ошибка
        if (curl_errno($ch)) {
            $this->logging(__METHOD__, $this->config->web_notification_subscribe_url, $credentials, curl_error($ch), 'api-error.log');
        }

        // Завершение сеанса и освобождение ресурсов
        curl_close($ch);

        return $response;
    }
}