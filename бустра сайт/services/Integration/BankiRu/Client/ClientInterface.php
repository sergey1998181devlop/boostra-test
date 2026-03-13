<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Client;

interface ClientInterface
{
    public function request(string $method, string $url, array $params = [], array $headers = []): array;
}