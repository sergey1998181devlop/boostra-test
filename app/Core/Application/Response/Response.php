<?php

namespace App\Core\Application\Response;

class Response
{
    public const HTTP_OK = 200;
    public const HTTP_PERMISSION_DENIED = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;

    protected $content;
    protected $statusCode = 200;
    protected array $headers = [];

    public function __construct($content = '', $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function make($content = '', $statusCode = 200, array $headers = []): Response
    {
        return new static($content, $statusCode, $headers);
    }

    public function content($content): Response
    {
        $this->content = $content;
        return $this;
    }

    public function statusCode($statusCode): Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function header($key, $value): Response
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        exit;
    }

    protected function sendHeaders()
    {
        if (!headers_sent()) {
            foreach ($this->headers as $key => $value) {
                header(sprintf('%s: %s', $key, $value));
            }
            http_response_code($this->statusCode);
        }
    }

    protected function sendContent()
    {
        echo $this->content;
    }

    public static function json($data, $statusCode = 200, array $headers = []): Response
    {
        return new static(json_encode($data), $statusCode, array_merge($headers, ['Content-Type' => 'application/json']));
    }
}
