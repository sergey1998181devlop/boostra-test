<?php

namespace App\Containers\DomainSection\Tickets\DTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;

class TsTicketStatusDTO implements DtoInterface
{
    private int $id;
    private string $name;
    private string $code;

    /**
     * @param int $id
     * @param string $name
     * @param string $code
     */
    public function __construct(int $id, string $name, string $code)
    {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}