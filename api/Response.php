<?php

require_once('Simpla.php');

class Response extends Simpla
{
    /**
     * Отправляет JSON-ответ с возможностью указания кастомных заголовков
     *
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @return void
     */
    public function json(array $data, int $statusCode = 200, array $headers = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function success($data = null, array $headers = []): void
    {
        self::json([
            'status' => 'success',
            'data' => $data
        ], 200, $headers);
    }

    public function error(string $message, int $statusCode = 400, array $headers = []): void
    {
        self::json([
            'status' => 'error',
            'message' => $message
        ], $statusCode, $headers);
    }
}