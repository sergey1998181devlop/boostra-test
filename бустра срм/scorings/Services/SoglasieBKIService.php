<?php

require_once(__DIR__ . '/../../api/Simpla.php');
require_once(__DIR__ . '/../../api/S3ApiClient.php');

class SoglasieBKIService extends Simpla
{
    const SALT = 'a1b2c3d4e5f6g7h8i9j0';

    const URL = 'user/docs';

    const ACTION = 'soglasie_na_bki_finlab';

    const DIR = 'soglasie_na_bki_finlab';

    private string $host = '';

    private S3ApiClient $s3ApiClient;

    public function __construct()
    {
        parent::__construct();

        $this->s3ApiClient = new S3ApiClient($this->config->s3['SoglasieBKIBucket']);

        $this->host = rtrim($this->config->front_url, '/');
    }

    public function getContentForUser($userId)
    {
        return file_get_contents($this->buildUrl($userId));
    }

    private function buildUrl($userId): string
    {
        return sprintf(
            '%s/%s?%s',
            $this->host,
            self::URL,
            http_build_query([
                'action' => self::ACTION,
                'user_id' => $userId,
                'token' => $this->buildHash($userId),
            ])
        );
    }

    private function buildHash($userId)
    {
        return hash_hmac('sha256', $userId, self::SALT);
    }

    public function saveInS3($name, $content)
    {
        $patch = self::DIR . '/' . $name;
        $this->s3ApiClient->putFileBody($content, $patch);

        return $patch;
    }
}