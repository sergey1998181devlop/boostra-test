<?php

namespace App\Containers\DomainSection\Tickets\DTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;

class DirectionDTO implements DtoInterface
{
    private int $id;
    private string $name;
    private string $code;
    private bool $isActive;

    /**
     * @param int $id
     * @param string $name
     * @param string $code
     * @param bool $isActive
     */
    public function __construct(
        int $id = 0,
        string $name = '',
        string $code = '',
        bool $isActive = false
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->isActive = $isActive;
    }

    //region Getters and Setters
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }
    //endregion Getters and Setters

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'is_active' => $this->isActive
        ];
    }
}