<?php

namespace api\services;

require_once dirname(__DIR__) . '/../vendor/autoload.php';

use Aws\Credentials\Credentials;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use GuzzleHttp\Psr7\Stream;

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
     * Получает файл из хранилища по ключу
     * @param string $fileKey
     * @return Stream|null
     */
    public function getFile(string $fileKey): ?Stream
    {
        try {
            $result = $this->client->getObject(
                [
                    'Bucket' => $this->bucket,
                    'Key' => $fileKey,
                ]
            );

            return $result['Body'];
        } catch (Exception|S3Exception $e) {
            error_log('Ошибка при получении файла: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Загружает содержимое файла в хранилище
     * @param string $filePath
     * @param string $fileKey
     * @return Result
     */
    public function putFile(string $filePath, string $fileKey): Result
    {
        try {
            return $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $fileKey,
                'SourceFile' => $filePath
            ]);
        } catch (Exception|S3Exception $e) {
            error_log('Ошибка при загрузке файла: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Удаляет файл
     * @param string $filePath
     * @return Result
     */
    public function deleteFile(string $filePath): Result
    {
        try {
            return $this->client->deleteObject(
                [
                    'Bucket' => $this->bucket,
                    'Key' => $filePath,
                ]
            );
        } catch (Exception|S3Exception $e) {
            error_log('Ошибка при удалении файла: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Генерирует временную публичную ссылку для файла по ключу (на 1 час)
     * @param string $fileKey
     * @return string
     */
    public function getPublicUrl(string $fileKey): string
    {
        try {
            $cmd = $this->client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $fileKey,
            ]);

            $request = $this->client->createPresignedRequest($cmd, '+1 hour');

            return (string)$request->getUri();
        } catch (Exception|S3Exception $e) {
            error_log('Ошибка при генерации публичной ссылки файла: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Проверяет существование файла
     * @param string $fileKey
     * @return bool
     */
    public function fileExists(string $fileKey): bool
    {
        try {
            return $this->client->doesObjectExist($this->bucket, $fileKey);
        } catch (Exception|S3Exception $e) {
            error_log('Ошибка при проверке существования файла: ' . $e->getMessage());
            throw $e;
        }
    }
}