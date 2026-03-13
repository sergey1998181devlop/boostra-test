<?php

namespace App\Dto;

class TicketCompanyDto
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $name;

    /** @var bool */
    private $isActive;

    /**
     * @param int|null $id
     * @param string $name
     * @param bool $isActive
     */
    public function __construct(?int $id, string $name, bool $isActive)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Название компании не указано');
        }
        return new self(
            isset($data['id']) ? (int)$data['id'] : null,
            (string)$data['name'],
            !isset($data['is_active']) || $data['is_active']
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'is_active' => $this->isActive ? 1 : 0
        ];
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }
}