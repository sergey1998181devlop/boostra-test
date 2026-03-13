<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;

class NeuroRecordingService
{
    private string $extAuthUrl;
    private string $cmsStreamBase;
    private string $extUser;
    private string $extPass;
    private FileStorageFactory $storageFactory;
    private Client $http;
    public function __construct(FileStorageFactory $storageFactory)
    {
        $this->extUser = (string)config('services.neuro.ext_user');
        $this->extPass = (string)config('services.neuro.ext_pass');
        $this->extAuthUrl = (string)config('services.neuro.api.auth_url');
        $this->cmsStreamBase = (string)config('services.neuro.api.cms_stream_base');
        $this->storageFactory = $storageFactory;
        $this->http = new Client([
            'timeout' => 20,
            'connect_timeout' => 10,
            'http_errors' => false,
            'allow_redirects' => true,
        ]);
    }

    /**
     * @param string $callUuid
     * @param string $agentUuid
     * @return string|null
     * @throws Throwable
     */
    public function fetchAndStore(string $callUuid, string $agentUuid): ?string
    {
        log_info('Neuro fetchAndStore enter', [
            'call_id' => $callUuid,
            'agent_uuid' => $agentUuid,
        ]);

        $token = $this->authenticate();
        if (!$token) {
            log_error('Neuro auth failed', [
                'endpoint' => $this->extAuthUrl,
                'call_id' => $callUuid,
            ]);
            return null;
        }

        $audio = $this->downloadStream($callUuid, $agentUuid, $token);
        if ($audio === null) {
            log_error('Neuro download failed', [
                'url' => sprintf('%s/%s', $this->cmsStreamBase, $callUuid),
                'call_id' => $callUuid,
            ]);
            return null;
        }

        $binary = $audio['content'];
        $contentType = strtolower($audio['content_type'] ?? '');

        if ($binary === '') {
            log_warning('Neuro download empty body', [
                'url' => sprintf('%s/%s', $this->cmsStreamBase, $callUuid),
                'call_id' => $callUuid,
            ]);
            return null;
        }

        $ext = $this->detectExtensionFromMime($contentType);
        if ($ext === null) {
            log_warning('Unknown content type', [
                'reported_mime' => $contentType,
                'call_id' => $callUuid,
            ]);
            $ext = 'bin';
            if ($contentType === '') {
                $contentType = 'application/octet-stream';
            }
        }

        $storage = $this->storageFactory->make('call_records');
        $objectKey = 'neuro/' . date('Y/m/d/') . $callUuid . '.' . $ext;
        try {
            $savedKey = $storage->putBytes($binary, $objectKey, $contentType ?: 'application/octet-stream');
        } catch (Throwable $e) {
            log_error('Storage upload failed', [
                'disk_profile' => 'call_records',
                'path' => $objectKey,
                'size' => strlen($binary),
                'call_id' => $callUuid,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
        if ($savedKey) {
            return $savedKey;
        }

        log_error('Storage put returned empty key', [
            'disk_profile' => 'call_records',
            'path' => $objectKey,
            'size' => strlen($binary),
            'call_id' => $callUuid,
        ]);
        return null;
    }

    /**
     * @param string $playerUrl
     * @return string|null
     */
    public function extractCallUuid(string $playerUrl): ?string
    {
        if ($playerUrl === '') return null;
        // достаём UUID из ссылок от голосового ИИ, player/stream и logs?*call_uuid=...
        if (preg_match('~/(?:stream|STREAM)/([0-9a-f\-]{36})~i', $playerUrl, $m)) return $m[1];
        $parts = parse_url($playerUrl);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $q);
            if (!empty($q['url']) && preg_match('~/(?:stream|STREAM)/([0-9a-f\-]{36})~i', (string)$q['url'], $m2)) return $m2[1];
        }
        return null;
    }

    /**
     * @param string $callLogUrl
     * @return string|null
     */
    public function extractAgentUuid(string $callLogUrl): ?string
    {
        if ($callLogUrl === '') return null;
        // достаём UUID агента из query-параметра agent_uuid
        $parts = parse_url($callLogUrl);
        if (empty($parts['query'])) return null;
        parse_str($parts['query'], $q);
        $val = $q['agent_uuid'] ?? null;
        return is_string($val) && $val !== '' ? $val : null;
    }

    /**
     * @return string|null
     */
    private function authenticate(): ?string
    {
        if ($this->extUser === '' || $this->extPass === '') {
            log_error('Neuro auth credentials missing');
            return null;
        }
        try {
            $resp = $this->http->post($this->extAuthUrl, [
                'headers' => ['Accept' => 'application/json'],
                'auth' => [$this->extUser, $this->extPass],
            ]);
            if ($resp->getStatusCode() !== 200) {
                log_error('Neuro auth non-200', [
                    'http_status' => $resp->getStatusCode(),
                ]);
                return null;
            }
            $json = json_decode((string)$resp->getBody(), true);
            $token = $json['token'] ?? null;
            if (!$token) {
                log_error('Neuro auth no token in response');
            }
            return $token;
        } catch (GuzzleException $e) {
            log_error('Neuro auth exception', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * @param string $callUuid
     * @param string $agentUuid
     * @param string $token
     * @return string[]|null
     */
    private function downloadStream(string $callUuid, string $agentUuid, string $token): ?array
    {
        $urlBase = sprintf('%s/%s', $this->cmsStreamBase, rawurlencode($callUuid));
        $delays = $this->getRetryDelays();
        $reauthed = false;

        $query = [
            'auth_token' => $token,
            'agent_uuid' => $agentUuid,
        ];

        foreach ($delays as $i => $delay) {
            $resp = $this->performStreamRequest($urlBase, $query, $callUuid);
            if ($this->isSuccess($resp)) {
                return $this->buildAudioPayload($resp);
            }

            $status = (int)($resp['code'] ?? 0);
            if ($status === 401 && !$reauthed) {
                $newToken = $this->authenticate();
                if ($newToken) {
                    $query['auth_token'] = $newToken;
                    $reauthed = true;
                    continue;
                }
            }

            if ($this->shouldRetry($status) && $i < count($delays) - 1) {
                sleep($delay);
                continue;
            }

            // финальный провал
            if ($status !== 0) {
                log_error('Neuro download non-2xx', [
                    'url' => $urlBase,
                    'http_status' => $status,
                    'call_id' => $callUuid,
                ]);
            }
            break;
        }

        return null;
    }

    /**
     * @param string $url
     * @param array $query
     * @param string $callUuid
     * @return array
     */
    private function performStreamRequest(string $url, array $query, string $callUuid): array
    {
        try {
            $resp = $this->http->get($url, [
                'query' => $query,
                'timeout' => 60,
                'connect_timeout' => 10,
            ]);
            return [
                'code' => $resp->getStatusCode(),
                'body' => (string)$resp->getBody(),
                'type' => $resp->getHeaderLine('Content-Type'),
            ];
        } catch (GuzzleException $e) {
            log_error('Neuro download exception', [
                'url' => $url,
                'call_id' => $callUuid,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return ['code' => 0, 'body' => '', 'type' => ''];
        }
    }

    /**
     * @param array $resp
     * @return bool
     */
    private function isSuccess(array $resp): bool
    {
        $code = (int)($resp['code'] ?? 0);
        return $code >= 200 && $code < 300;
    }

    /**
     * @param array $resp
     * @return string[]
     */
    private function buildAudioPayload(array $resp): array
    {
        return [
            'content' => (string)($resp['body'] ?? ''),
            'content_type' => (string)($resp['type'] ?? ''),
        ];
    }

    /**
     * @return int[]
     */
    private function getRetryDelays(): array
    {
        return [2, 5, 15, 30];
    }

    /**
     * @param int $status
     * @return bool
     */
    private function shouldRetry(int $status): bool
    {
        if ($status === 0) return true;      // сетевой сбой
        if ($status === 401) return true;    // попробуем реавторизацию
        if ($status === 404) return true;    // запись может быть не готова
        if ($status === 429) return true;    // too many requests
        if ($status >= 500) return true;     // временные сбои
        return false;
    }

    /**
     * @param string $contentType
     * @return string|null
     */
    private function detectExtensionFromMime(string $contentType): ?string
    {
        $map = [
            'audio/ogg' => 'ogg',
            'audio/opus' => 'ogg',
            'audio/webm' => 'webm',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'audio/aac' => 'aac',
            'audio/mp4' => 'm4a',
            'audio/3gpp' => '3gp',
        ];
        return $map[$contentType] ?? null;
    }
}
