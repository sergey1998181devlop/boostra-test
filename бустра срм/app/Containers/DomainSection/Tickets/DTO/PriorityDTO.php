<?php

namespace App\Containers\DomainSection\Tickets\DTO;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;

class PriorityDTO implements DtoInterface
{
    //region Attributes
    private int $id;
    private string $name;
    private string $color;
    //endregion Attributes

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

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }
    //endregion Getters and Setters

    public function __construct(
        int $id = 0,
        string $name = '',
        string $color = ''
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
        ];
    }
}