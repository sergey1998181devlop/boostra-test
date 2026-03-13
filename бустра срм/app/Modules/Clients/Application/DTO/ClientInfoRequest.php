<?php

namespace App\Modules\Clients\Application\DTO;

class ClientInfoRequest
{
    private string $phone;
    /** @var int[]|null */
    private ?array $organizationIds;
    private bool $allOrders;

    /**
     * @param string $phone
     * @param int|int[]|null $organizationIds Single ID, array of IDs, or null
     * @param bool $allOrders
     */
    public function __construct(string $phone, $organizationIds = null, bool $allOrders = false)
    {
        $this->phone = $phone;
        $this->organizationIds = self::normalizeOrganizationIds($organizationIds);
        $this->allOrders = $allOrders;
    }

    public static function fromArray(array $data): self
    {
        $orgIds = $data['organizationIds'] ?? $data['organizationId'] ?? null;

        return new self(
            $data['phone'] ?? '',
            $orgIds,
            (bool)($data['all_orders'] ?? false)
        );
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getFormattedPhone(): string
    {
        return preg_replace('/[^0-9]/', '', $this->phone);
    }

    /**
     * @deprecated Use getOrganizationIds() instead
     * @return int|null Returns first organization ID for backward compatibility
     */
    public function getOrganizationId(): ?int
    {
        if (empty($this->organizationIds)) {
            return null;
        }

        return $this->organizationIds[0];
    }

    /**
     * @return int[]|null
     */
    public function getOrganizationIds(): ?array
    {
        return $this->organizationIds;
    }

    public function getAllOrders(): bool
    {
        return $this->allOrders;
    }

    /**
     * @param int|int[]|null $value
     * @return int[]|null
     */
    private static function normalizeOrganizationIds($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $ids = array_map('intval', array_filter($value, function ($v) {
                return is_numeric($v);
            }));
            return empty($ids) ? null : array_values($ids);
        }

        if (is_numeric($value)) {
            return [(int)$value];
        }

        return null;
    }
}