<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once('Simpla.php');

use Aws\Result;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;

/**
 * Оболочка для работы с S3
 */
class S3ApiClient extends Simpla
{
    private S3Client $s3_client;

    private string $bucket;

    public function __construct(string $bucket = '', string $configKey = 's3')
    {
        parent::__construct();

        $s3Config = $this->config->$configKey;

        $this->s3_client = new S3Client(
            [
                'version' => 'latest',
                'region' => $s3Config['region'],
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $s3Config['key'],
                    'secret' => $s3Config['secret'],
                ],
                'endpoint' => $s3Config['endpoint'],
                'suppress_php_deprecation_warning' => true,
            ]
        );

        $this->bucket = $bucket ?: $s3Config['Bucket'];
    }

    public function setBucket(string $bucket = ''): S3ApiClient
    {
        $this->bucket = $bucket;

        return $this;
    }

    /**
     * Возвращает файл в бинарном виде
     * @param string $file_path
     * @return Stream
     * https://docs.guzzlephp.org/en/stable/psr7.html#streams
     */
    public function getFileContent(string $file_path): Stream
    {
        $result = $this->s3_client->getObject(
            [
                'Bucket' => $this->bucket,
                'Key' => $file_path,
            ]
        );

        return $result['Body'];
    }

    /**
     * Отправляет файл в хранилище
     * @param string $file_path
     * @param string $fileName
     * @return Result
     */
    public function putFileContent(string $file_path, string $fileName): Result
    {
        return $this->s3_client->putObject(
            [
                'Bucket' => $this->bucket,
                'Key' => $fileName,
                'SourceFile' => $file_path,
            ]
        );
    }

    /**
     * Отправляет файл в хранилище
     * @param $body
     * @param string $fileName
     * @return Result
     */
    public function putFileBody($body, string $fileName): Result
    {
        return $this->s3_client->putObject(
            [
                'Bucket' => $this->bucket,
                'Key' => $fileName,
                'Body' => $body,
            ]
        );
    }

    /**
     * Удаляет файл
     * @param string $file_path
     * @return Result
     */
    public function deleteFile(string $file_path): Result
    {
        return $this->s3_client->deleteObject(
            [
                'Bucket' => $this->bucket,
                'Key' => $file_path,
            ]
        );
    }

    public function buildUrl(string $s3Name): string
    {
        return $this->s3_client->getObjectUrl($this->bucket, $s3Name);
    }

    public function getPublicUrl(string $fileKey, $expires = '+3 hour', string $downloadName = ''): string
    {
        $params = [
            'Bucket' => $this->bucket,
            'Key'    => $fileKey,
        ];

        if ($downloadName !== '') {
            $params['ResponseContentDisposition'] = 'attachment; filename="' . $downloadName . '"';
        }

        $cmd = $this->s3_client->getCommand('GetObject', $params);

        $request = $this->s3_client->createPresignedRequest($cmd, $expires);

        return (string)$request->getUri();
    }
}
