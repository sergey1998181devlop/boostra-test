<?php

namespace App\Dto;

/**
 * DTO для записи Vox DNC по сайту (s_vox_site_dnc).
 * Преобразование из строки БД и в массив для ответа API.
 */
class VoxSiteDncDto
{
    /** @var int */
    public int $id = 0;

    /** @var string */
    public string $siteId = '';

    /** @var int */
    public int $organizationId = 0;

    /** @var string|null */
    public ?string $voxDomain;

    /** @var string|null */
    public ?string $voxToken;

    /** @var string|null */
    public ?string $apiUrl;

    /** @var int|null */
    public ?int $outgoingCallsDncListId;

    /** @var int */
    public int $isActive = 0;

    /** @var string|null */
    public ?string $comment;

    /** @var string|null */
    public ?string $createdAt;

    /** @var string|null */
    public ?string $updatedAt;

    /**
     * Создать DTO из строки БД (object из репозитория).
     *
     * @param object $row
     * @return self
     */
    public static function fromRow(object $row): self
    {
        $dto = new self();
        $dto->id = (int)$row->id;
        $dto->siteId = (string)$row->site_id;
        $dto->organizationId = (int)$row->organization_id;
        $dto->voxDomain = $row->vox_domain !== null ? (string)$row->vox_domain : null;
        $dto->voxToken = $row->vox_token !== null ? (string)$row->vox_token : null;
        $dto->apiUrl = $row->api_url !== null ? (string)$row->api_url : null;
        $dto->outgoingCallsDncListId = $row->outgoing_calls_dnc_list_id !== null
            ? (int)$row->outgoing_calls_dnc_list_id
            : null;
        $dto->isActive = (int)$row->is_active;
        $dto->comment = $row->comment !== null ? (string)$row->comment : null;
        $dto->createdAt = $row->created_at ?? null;
        $dto->updatedAt = $row->updated_at ?? null;
        return $dto;
    }

    /**
     * Преобразовать в массив для ответа API (snake_case ключи).
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'site_id' => $this->siteId,
            'organization_id' => $this->organizationId,
            'vox_domain' => $this->voxDomain,
            'vox_token' => $this->voxToken,
            'api_url' => $this->apiUrl,
            'outgoing_calls_dnc_list_id' => $this->outgoingCallsDncListId,
            'is_active' => $this->isActive,
            'comment' => $this->comment,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
