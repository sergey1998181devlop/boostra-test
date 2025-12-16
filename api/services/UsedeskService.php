<?php

namespace api\services;

use CURLFile;
use Exception;
use Simpla;

class UsedeskService extends Simpla
{
    private string $api_url;
    private const ENDPOINT_CREATE_TICKET = 'create/ticket';
    private const ENDPOINT_CREATE_COMMENT = 'create/comment';
    private const ENDPOINT_CREATE_CLIENT = 'create/client';
    private const ENDPOINT_LIST_TICKETS = 'tickets';
    private int $timeout = 10;


    public function __construct()
    {
        parent::__construct();

        $this->api_url = $this->config->USEDESK['API_URL'];
    }

    /**
     * Execute cURL request
     *
     * @throws Exception
     */
    private function request(string $endpoint, string $method, array $data, string $apiToken): array
    {
        $url = "$this->api_url/$endpoint";
        $body = json_encode(array_merge(['api_token' => $apiToken], $data));

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => in_array($method, ['POST', 'PUT', 'PATCH'], true) ? $body : null,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            throw new Exception("Usedesk API error [$status]: " . ($error ?: $response));
        }

        return json_decode($response, true) ?: [];
    }

    /**
     * Execute cURL request multipart
     *
     * @throws Exception
     */
    private function requestMultipart(string $endpoint, array $data, array $files, string $apiToken): array
    {
        $url = "$this->api_url/$endpoint";
        $postFields = ['api_token' => $apiToken] + $data;

        foreach ($files as $id => $file) {
            $postFields["files[$id]"] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            throw new Exception("Usedesk API error [$status]: " . ($error ?: $response));
        }

        return json_decode($response, true) ?: [];
    }

    private function buildParams(array $params): array
    {
        return array_filter($params, fn($value) => $value !== null && $value !== '');
    }

    /**
     * @throws Exception
     */
    public function createTicket(string $apiToken, array $data, array $files = []): array
    {
        if (!empty($files)) {
            return $this->requestMultipart(self::ENDPOINT_CREATE_TICKET, $data, $files, $apiToken);
        }

        return $this->request(self::ENDPOINT_CREATE_TICKET, 'POST', $data, $apiToken);
    }

    /**
     * @throws Exception
     */
    public function createComment(
        string $apiToken,
        int    $ticketId,
        string $message,
        array  $options = [],
        array  $files = []
    ): array
    {
        $params = array_merge(['ticket_id' => $ticketId, 'message' => $message], $options);

        if (!empty($files)) {
            return $this->requestMultipart(self::ENDPOINT_CREATE_COMMENT, $this->buildParams($params), $files, $apiToken);
        }

        return $this->request(self::ENDPOINT_CREATE_COMMENT, 'POST', $this->buildParams($params), $apiToken);
    }

    /**
     * @throws Exception
     */
    public function createClient(
        string  $apiToken,
        string  $name,
        ?string $email = null,
        ?string $phone = null,
        ?string $note = null,
        ?string $profileUrl = null
    ): array
    {
        $params = ['name' => $name];
        if ($email !== null && trim($email) !== '') {
            $params['emails'] = [$email];
        }
        if ($phone !== null) {
            $params['phone'] = $phone;
        }
        if ($note !== null) {
            $params['note'] = $note;
        }
        if ($profileUrl !== null) {
            $params['sites'] = [['url' => $profileUrl]];
        }

        return $this->request(self::ENDPOINT_CREATE_CLIENT, 'POST', $params, $apiToken);
    }

    /**
     * Получить последний тикет по email клиента
     *
     * @throws Exception
     */
    public function getLastTicketByEmail(string $email, string $apiToken): ?int
    {
        try {
            $tickets = $this->request(self::ENDPOINT_LIST_TICKETS, 'POST', [
                'sort' => 'last_updated_at',
                'order' => 'desc',
                'limit' => 100
            ], $apiToken);

            if (empty($tickets)) {
                return null;
            }

            $email = strtolower($email);

            $filteredTickets = array_filter($tickets, function($ticket) use ($email) {
                return isset($ticket['channel_email']) && strtolower($ticket['channel_email']) === $email;
            });

            if (empty($filteredTickets)) {
                return null;
            }

            $lastTicket = reset($filteredTickets);

            return $lastTicket['id'] ?? null;
        } catch (Exception $e) {
            $this->logging('ERROR', 'UsedeskService::getLastTicketByEmail error', '', [
                'email' => $email,
                'error' => $e->getMessage()
            ], 'usedesk_api.txt');
            return null;
        }
    }

}
