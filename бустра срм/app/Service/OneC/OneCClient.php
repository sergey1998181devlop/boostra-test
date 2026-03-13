<?php

namespace App\Service\OneC;

use SoapClient;

class OneCClient
{
    private string $baseUrl;
    private string $db;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.1c.url');
        $this->db = (string) config('services.1c.db');
    }

    public function getContractDocuments(string $number): array
    {
        if ($this->baseUrl === '' || $this->db === '') {
            return [];
        }

        $client = new SoapClient($this->baseUrl . $this->db . "/ws/Tinkoff.1cws?wsdl", [
            'trace' => 0,
            'exceptions' => true,
            'connection_timeout' => 20,
        ]);

        $resp = $client->__soapCall('ContractRepository', [(object)['Номер' => $number]]);

        $raw = $resp->return ?? '';
        $list = json_decode($raw, true);

        return is_array($list) ? $list : [];
    }

    /**
     * @param array $orders
     * @param string $context 'batch' для массовых операций, 'single' для одиночных
     * @return array|null
     */
    public function checkOrderStatuses(array $orders, string $context = 'single'): ?array
    {
        if (empty($orders) || $this->baseUrl === '' || $this->db === '') {
            return null;
        }

        $isBatch = $context === 'batch';

        try {
            $client = new SoapClient(
                $this->baseUrl . $this->db . "/ws/WebZayavki.1cws?wsdl",
                [
                    'trace' => 1,
                    'exceptions' => true,
                    'connection_timeout' => 20,
                ]
            );

            $request = [
                'JsonData' => json_encode($orders, JSON_UNESCAPED_UNICODE)
            ];

            $response = $client->__soapCall('CheckOrderStatus', array($request));

            $result = json_decode($response->return ?? '{}', true);

            if (!$isBatch) {
                logger('api')->info('1C CheckOrderStatus', [
                    'orders_count' => count($orders),
                    'request' => $request,
                    'response' => $result,
                ]);
            }

            return $result;

        } catch (\SoapFault $e) {
            logger('api')->error('1C CheckOrderStatus SOAP error', [
                'error' => $e->getMessage(),
                'orders_count' => count($orders),
                'context' => $context,
                'orders_sent' => $isBatch ? null : $orders,
                'request' => $isBatch ? null : ($request ?? null),
            ]);
            return null;
        } catch (\Exception $e) {
            logger('api')->error('1C CheckOrderStatus error', [
                'error' => $e->getMessage(),
                'orders_count' => count($orders),
                'context' => $context,
                'orders_sent' => $isBatch ? null : $orders,
                'request' => $isBatch ? null : ($request ?? null),
            ]);
            return null;
        }
    }
}


