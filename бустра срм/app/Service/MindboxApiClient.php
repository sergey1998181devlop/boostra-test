<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Exception;
use GuzzleHttp\Psr7\Utils;

class MindboxApiClient
{
    private $config;
    private Client $httpClient;

    public function __construct($config)
    {
        $this->config = $config;
        $this->httpClient = new Client([
            'timeout' => 300,
            'connect_timeout' => 30,
        ]);
    }

    /**
     * Отправка данных через стриминг
     * @param string $csvContent
     * @param string $transactionId
     * @param string $operationName
     * @return array
     * @throws Exception
     */
    public function sendBatchStream(string $csvContent, string $transactionId, string $operationName): array
    {
        $url = $this->buildApiUrl($transactionId, $operationName);

        $this->logMindbox('sendBatchStream: request', [
            'url' => $url,
            'operation' => $operationName,
            'transaction_id' => $transactionId,
        ]);

        try {
            $stream = Utils::streamFor($csvContent);

            $response = $this->httpClient->post($url, [
                'headers' => [
                    'Authorization' => 'SecretKey ' . $this->config->mb_secret_key,
                    'Accept' => 'application/json',
                    'Content-Type' => 'text/csv;charset=utf-8',
                ],
                'body' => $stream,
            ]);

            $httpCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            $this->logMindbox('sendBatchStream: response', [
                'http_code' => $httpCode,
                'response' => $responseData,
            ]);

            return [
                'http_code' => $httpCode,
                'response' => $responseData
            ];
        } catch (ConnectException $e) {
            $this->logMindbox('sendBatchStream: connection failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ], 'error');
            throw new Exception("Connection failed: " . $e->getMessage());
        } catch (RequestException $e) {
            $httpCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorMessage = $e->getMessage();
            $body = $e->hasResponse() ? (string)$e->getResponse()->getBody() : '';

            $this->logMindbox('sendBatchStream: request failed', [
                'http_code' => $httpCode,
                'error' => $errorMessage,
                'body' => $body ? substr($body, 0, 500) : null,
                'transaction_id' => $transactionId,
            ], 'error');

            if ($httpCode === 429) {
                throw new Exception("HTTP Error 429: Превышен лимит запросов. Попробуйте позже.");
            }

            throw new Exception("HTTP Error $httpCode: $errorMessage");
        } catch (\Throwable $e) {
            $this->logMindbox('sendBatchStream: unexpected error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ], 'error');
            throw new Exception("Unexpected error: " . $e->getMessage());
        }
    }

    /**
     * Логирование запросов к Mindbox API
     * @param string $message
     * @param array $context
     * @param string $level
     */
    private function logMindbox(string $message, array $context = [], string $level = 'info'): void
    {
        $line = '[MindboxApi] ' . $message;
        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        error_log($line);
        if (function_exists('logger')) {
            try {
                logger('mindbox')->{$level}($message, $context);
            } catch (\Throwable $e) {
                // ignore if Application not bootstrapped
            }
        }
    }


    /**
     * Построение URL для API
     * @param string $transactionId
     * @param string $operationName
     * @return string
     */
    private function buildApiUrl(string $transactionId, string $operationName): string
    {
        $params = [
            'endpointId' => $this->config->mb_endpoint_id,
            'operation' => $operationName,
            'csvCodePage' => '65001', // UTF-8
            'csvColumnDelimiter' => ';',
            'csvTextQualifier' => '"',
            'SourceActionTemplate' => 'ImportAPI',
            'transactionId' => $transactionId
        ];

        return 'https://api.mindbox.ru/v3/operations/bulk?' . http_build_query($params);
    }
}