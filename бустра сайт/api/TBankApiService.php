<?php

require_once 'Simpla.php';
require_once '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TBankApiService extends Simpla
{
    const API_URL = 'https://securepay.tinkoff.ru/v2/';

    const API_URL_TEST = 'https://rest-api-test.tinkoff.ru/v2/';

    private Client $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => $this->config->is_dev ? self::API_URL_TEST : self::API_URL,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function initRequest(array $params): array
    {
        $url = $this->config->is_dev ? self::API_URL_TEST : self::API_URL;
        try {
            $response = $this->client->post('Init', [
                'json' => $params,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logging(__METHOD__, $url.'Init', $params, json_decode($response->getBody()->getContents(), true), date('d-m-Y').'-t-bank-error.txt');

                return [];
            }

            $response = json_decode($response->getBody()->getContents(), true);
            $this->logging(__METHOD__, $url.'Init', $params, $response, date('d-m-Y').'-t-bank-error.txt');
            return $response;
        } catch (RequestException $e) {
            $this->logging(__METHOD__, $url.'Init', $params, json_decode($e->getResponse()->getBody()->getContents(), true), date('d-m-Y').'-t-bank-error.txt');

            return [];
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, $url.'Init', $params, ['error' => $e->getMessage()], date('d-m-Y').'-t-bank-error.txt');

            return [];
        }
    }

    public function getQrRequest(array $params): array
    {
        $url = $this->config->is_dev ? self::API_URL_TEST : self::API_URL;
        try {
            $response = $this->client->post('GetQr', [
                'json' => $params,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logging(__METHOD__, $url.'GetQr', $params, json_decode($response->getBody()->getContents(), true), date('d-m-Y').'-t-bank-error.txt');

                return [];
            }

            $response = json_decode($response->getBody()->getContents(), true);
            $this->logging(__METHOD__, $url.'GetQr', $params, $response, date('d-m-Y').'-t-bank-error.txt');
            return $response;
        } catch (RequestException $e) {
            $this->logging(__METHOD__, $url.'GetQr', $params, json_decode($e->getResponse()->getBody()->getContents(), true), date('d-m-Y').'-t-bank-error.txt');

            return [];
        } catch (Throwable $e) {
            $this->logging(__METHOD__, $url.'GetQr', $params, ['error' => $e->getMessage()], date('d-m-Y').'-t-bank-error.txt');

            return [];
        }
    }

    public function addAccountQrRequest(array $params): array
    {
        $url = $this->config->is_dev ? self::API_URL_TEST : self::API_URL;

        try {
            $response = $this->client->post('AddAccountQr', [
                'json' => $params,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logging(__METHOD__, $url.'AddAccountQr', $params, json_decode($response->getBody()->getContents(), true), date('d-m-Y').'-t-bank-error.txt');

                return [];
            }

            $response = json_decode($response->getBody()->getContents(), true);
            $this->logging(__METHOD__, $url.'AddAccountQr', $params, $response, date('d-m-Y').'-t-bank-error.txt');
            return $response;
        } catch (RequestException $e) {
            $this->logging(__METHOD__, $url.'AddAccountQr', $params, json_decode($e->getResponse()->getBody()->getContents(), true), date('d-m-Y').'-t-bank-error.txt');

            return [];
        } catch (Throwable $e) {
            $this->logging(__METHOD__, $url.'AddAccountQr', $params, ['error' => $e->getMessage()], date('d-m-Y').'-t-bank-error.txt');

            return [];
        }
    }
}