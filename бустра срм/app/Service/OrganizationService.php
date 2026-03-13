<?php

declare(strict_types=1);

namespace App\Service;

use Managers;

use function array_key_first;
use function config;

final class OrganizationService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $organizations;

    private int $defaultOrganizationId;

    private string $defaultDomain;

    private string $defaultToken;

    private string $apiUrlV3;

    /**
     * @var array<string, int>
     */
    private array $campaignToOrganization = [];

    public function __construct(?array $config = null)
    {
        $config = $config ?? (array) config('services.voximplant', []);

        $this->defaultDomain = (string) ($config['domain'] ?? 'boostra2023');
        $this->defaultToken = (string) ($config['token'] ?? '');
        $this->apiUrlV3 = (string) ($config['api_url_v3'] ?? 'https://kitapi-ru.voximplant.com/api/v3');

        $this->organizations = [];
        foreach (($config['organizations'] ?? []) as $id => $organization) {
            $organizationId = (int) $id;
            $this->organizations[$organizationId] = $organization;
        }

        $defaultId = (int) ($config['default_organization_id'] ?? 0);
        if (!isset($this->organizations[$defaultId]) && !empty($this->organizations)) {
            $defaultId = (int) array_key_first($this->organizations);
        }
        $this->defaultOrganizationId = $defaultId;

        $this->hydrateCampaignMap();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return $this->organizations;
    }

    public function getOptions(): array
    {
        $options = [];

        foreach ($this->organizations as $id => $organization) {
            $options[] = [
                'id' => (int) $id,
                'code' => $organization['code'] ?? (string) $id,
                'label' => $organization['label'] ?? ($organization['code'] ?? (string) $id),
            ];
        }

        return $options;
    }

    public function getDefaultId(): int
    {
        return $this->defaultOrganizationId;
    }

    public function resolveOrganizationId(?int $organizationId): int
    {
        if ($organizationId !== null && isset($this->organizations[$organizationId])) {
            return $organizationId;
        }

        return $this->defaultOrganizationId;
    }

    public function resolveOrganizationIdByCode(string $code): ?int
    {
        $normalized = strtoupper(trim($code));

        if ($normalized === '') {
            return null;
        }

        foreach ($this->organizations as $id => $organization) {
            $organizationCode = strtoupper((string)($organization['code'] ?? ''));

            if ($organizationCode === $normalized) {
                return (int) $id;
            }
        }

        return null;
    }

    public function exists(int $organizationId): bool
    {
        return isset($this->organizations[$organizationId]);
    }

    public function getLabel(int $organizationId): string
    {
        $organization = $this->get($organizationId);

        if ($organization === null) {
            return (string) $organizationId;
        }

        return $organization['label'] ?? ($organization['code'] ?? (string) $organizationId);
    }

    /**
     * @return array<string, string>
     */
    public function getVoxCredentials(int $organizationId): array
    {
        $organization = $this->get($organizationId);
        if ($organization === null) {
            return $this->getDefaultVoxCredentials();
        }

        $vox = $organization['vox'] ?? [];

        $domain = (string) ($vox['domain'] ?? $this->defaultDomain);
        $token = (string) ($vox['token'] ?? $this->defaultToken);

        return [
            'domain' => $domain ?: $this->defaultDomain,
            'token' => $token ?: $this->defaultToken,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getDefaultVoxCredentials(): array
    {
        return [
            'domain' => $this->defaultDomain,
            'token' => $this->defaultToken,
        ];
    }

    public function resolveOrganizationIdByCampaign(?string $campaignId): ?int
    {
        if ($campaignId === null || $campaignId === '') {
            return null;
        }

        return $this->campaignToOrganization[(string) $campaignId] ?? null;
    }

    public function resolveOrganizationIdByManager(int $managerId): ?int
    {
        $managers = new Managers();
        $company = $managers->getCompany($managerId);

        if (empty($company)) {
            return null;
        }

        if (is_object($company) && isset($company->company)) {
            $company = $company->company;
        }

        return $this->resolveOrganizationIdByCampaign((string) $company);
    }

    public function getApiUrlV3(): string
    {
        return $this->apiUrlV3;
    }

    public function get(int $organizationId): ?array
    {
        return $this->organizations[$organizationId] ?? null;
    }

    /**
     * Получить ID кампании для перезвона (callback) для организации
     */
    public function getCallbackCampaignId(int $organizationId): ?string
    {
        $organization = $this->get($organizationId);
        if ($organization === null) {
            return null;
        }

        $campaigns = $organization['vox']['campaigns'] ?? [];
        $callbackCampaignId = $campaigns['callback'] ?? null;

        return $callbackCampaignId ? (string) $callbackCampaignId : null;
    }

    private function hydrateCampaignMap(): void
    {
        foreach ($this->organizations as $organizationId => $organization) {
            $campaignGroups = (array) ($organization['vox']['campaigns'] ?? []);

            foreach ($campaignGroups as $campaigns) {
                if (is_array($campaigns)) {
                    foreach ($campaigns as $campaignId) {
                        $this->mapCampaignToOrganization($campaignId, $organizationId);
                    }
                } else {
                    $this->mapCampaignToOrganization($campaigns, $organizationId);
                }
            }
        }
    }

    private function mapCampaignToOrganization($campaignId, int $organizationId): void
    {
        if ($campaignId === null || $campaignId === '') {
            return;
        }

        $campaignKey = (string) $campaignId;
        $this->campaignToOrganization[$campaignKey] = $organizationId;
    }
}



