<?php

namespace App\Core\Application\Request;

use App\Core\Application\Traits\Singleton;

class Request {
    use Singleton;

    private array $params;

    private function __construct() {}

    /**
     * Get requested url.
     */
    public function path(): ?string {
        $url = parse_url($_SERVER['REQUEST_URI']);
        return trim($url['path']) ?? null;
    }

    /**
     * Get requested query params.
     */
    public function query(string $param = '') {
        $url   = parse_url($_SERVER['REQUEST_URI']);
        $query = $url['query'] ?? null;
        parse_str($query, $params);

        if ($param) {
            return $params[$param] ?? null;
        }
        return $params;
    }

    /**
     * Get requested method.
     */
    public function method(): string {
        $method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        return strtolower($method);
    }

    /**
     * Checks and returns boolean whether the method is GET.
     */
    public function isGet(): string {
        return $this->method() === 'get';
    }

    /**
     * Checks and returns boolean whether the method is POST.
     */
    public function isPost(): string {
        return $this->method() === 'post';
    }

    /**
     * Checks and returns boolean whether the method is DELETE.
     */
    public function isDelete(): string {
        return $this->method() === 'delete';
    }

    /**
     * Checks and returns boolean whether the method is PUT.
     */
    public function isPut(): string {
        return $this->method() === 'put';
    }

    /**
     * Checks and returns boolean whether the method is PATCH.
     */
    public function isPatch(): string {
        return $this->method() === 'patch';
    }

    /**
     * Retrieve all requested data., you can also get single data from here.
     * If no data is found, the default data will be returned.
     */
    public function input(?string $key = null, $default = null)
    {
        if ($key) {
            if (isset($_REQUEST[$key])) {
                return strip_tags($_REQUEST[$key]);
            }
        } else {
            $input = [];

            foreach ($_REQUEST as $index => $value) {
                $input[$index] = strip_tags($value);
            }
            return $input;
        }
        return $default;
    }

    /**
     * Retrieve all requested data.
     */
    public function all()
    {
        $all = [];

        foreach ($_REQUEST as $key => $value) {
            $all[$key] = strip_tags($value);
        }
        return $all;
    }

    /**
     * Retrieve some specific data from all data requested.
     */
    public function only()
    {
        $only = [];
        $args = func_get_args();

        foreach ($args as $key) {
            if (isset($_REQUEST[$key])) {
                $only[$key] = strip_tags($_REQUEST[$key]);
            }
        }
        return $only;
    }

    /**
     * Set params.
     */
    public function setParams($value): void
    {
        $this->params = $value;
    }

    /**
     * Get param.
     */
    public function getParam(?string $key = null)
    {
        if ($key) {
            return $this->params[$key] ?? null;
        } elseif (!empty($this->params)) {
            return $this->params;
        } else {
            return null;
        }
    }

    public function headers(?string $key = null)
    {
        $headers = [];

        // Check if the function exists to ensure compatibility.
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback if getallheaders() is not defined.
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }

        if ($key) {
            $key = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $key)))); // Normalize key to match the expected format
            return $headers[$key] ?? null;
        }

        return $headers;
    }

    public function bearerToken()
    {
        $header = $this->headers('Authorization');

        $position = strrpos($header, 'Bearer ');

        if ($position !== false) {
            $header = substr($header, $position + 7);

            return str_contains($header, ',') ? strstr($header, ',', true) : $header;
        }
    }

    public function json(?string $key = null, $default = null)
    {
        $json = file_get_contents('php://input');

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $default;
        }

        if ($key) {
            return $data[$key] ?? $default;
        }

        return $data;
    }
}
