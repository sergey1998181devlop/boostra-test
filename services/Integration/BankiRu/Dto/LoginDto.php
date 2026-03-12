<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Dto;

use JsonSerializable;

final class LoginDto implements JsonSerializable
{
    public $token;
    public $type;
    public $status;
    public $errors = null;

    public function __construct(string $token, string $type, string $status, ?array $errors = null)
    {
        $this->token = $token;
        $this->type = $type;
        $this->status = $status;
        $this->errors = $errors;

    }

    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'type' => $this->type,
            'status' => $this->status,
            'errors' => $this->errors,
        ];
    }
}
