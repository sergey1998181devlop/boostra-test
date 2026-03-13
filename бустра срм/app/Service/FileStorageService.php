<?php

namespace App\Service;

use Aws\Credentials\Credentials;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;

class FileStorageService
{
    private string $endpoint;
    private string $region;
    private string $accessKey;
    private string $secretKey;
    private string $bucket;
    private Credentials $credentials;
    private S3Client $client;

    public function __construct(string $endpoint, string $region, string $accessKey, string $secretKey, string $bucket)
    {
        $this->endpoint = $endpoint;
        $this->region = $region;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->bucket = $bucket;

        $this->credentials = new Credentials($this->accessKey, $this->secretKey);
        $this->client = new S3Client([
            'region' => $this->region,
            'version' => 'latest',
            'credentials' => $this->credentials,
            'endpoint' => $this->endpoint,
            'use_path_style_endpoint' => true,
            'suppress_php_deprecation_warning' => true
        ]);
    }

    /**
     * Загружает файл в хранилище и возвращает ключ файла
     * @param array $file - $_FILES['file'] или аналогичный массив
     * @return string|null - ключ файла или null в случае ошибки
     */
    public function uploadFile(array $file, string $prefix = ''): ?string
    {
        if (!isset($file['tmp_name'])) {
            return null;
        }

        $fileKey = $prefix . uniqid() . '_' . basename($file['name']);

        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $fileKey,
                'SourceFile' => $file['tmp_name'],
                'ACL' => 'private',
            ]);
            return $fileKey;
        } catch (Exception|S3Exception $e) {
            error_log('Ошибка при загрузке файла: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Загрузка произвольного бинарного содержимого в хранилище
     * @param string $bytes
     * @param string $objectKey
     * @param string $contentType
     * @return string|null - ключ файла или null в случае ошибки
     */
    public function putBytes(string $bytes, string $objectKey, string $contentType = 'application/octet-stream'): ?string
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $objectKey,
                'Body' => $bytes,
                'ACL' => 'private',
                'ContentType' => $contentType,
            ]);
            return $objectKey;
        } catch (Exception|S3Exception $e) {
            error_log('Ошибка при загрузке бинарных данных: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Скачивает файл по ключу из хранилища
     * @param string $objectKey
     * @return string|null - содержимое файла или null в случае ошибки
     */
    public function downloadFile(string $objectKey): string
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $objectKey,
            ]);

            return (string)$result['Body'];
        } catch (Exception|S3Exception $e) {
            if (method_exists($e, 'getStatusCode') && $e->getStatusCode() === 404) {
                return '';
            }

            error_log('Ошибка при скачивании файла: ' . $e->getMessage());
            return '';
        }
    }

    public function downloadFileByUrl(string $fileUrl): string
    {
        try {
            $parsedUrl = parse_url($fileUrl);

            if (!isset($parsedUrl['path'])) {
                throw new Exception('Некорректный URL.');
            }

            $fileUrl = preg_replace('/^\/?' . $this->bucket . '\//', '', $parsedUrl['path']);

            return $this->downloadFile($fileUrl);
        } catch (Exception $e) {
            error_log("Ошибка при скачивании файла по ссылке: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Генерирует временную публичную ссылку для файла по ключу (на 1 час)
     * @param string $fileKey
     * @return string
     */
    public function getPublicUrl(string $fileKey): string
    {
        $cmd = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $fileKey,
        ]);

        $request = $this->client->createPresignedRequest($cmd, '+1 hour');

        return (string)$request->getUri();
    }

    /**
     * Генерирует временную публичную ссылку для просмотра файла без скачивания
     * @param string $fileKey
     * @return string
     */
    public function getViewUrl(string $fileKey): string
    {
        $cmd = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $fileKey,
            'ResponseContentDisposition' => 'inline',
        ]);

        $request = $this->client->createPresignedRequest($cmd, '+1 hour');

        return (string)$request->getUri();
    }
}