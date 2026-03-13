<?php

namespace App\Service;

use App\Dto\VoxSiteDncDto;
use App\Repositories\VoxSiteDncRepository;
use Exception;

/**
 * Сервис для CRUD по s_vox_site_dnc (Vox DNC по сайту — отключение звонков робота).
 */
class VoxSiteDncService
{
    /** @var VoxSiteDncRepository */
    private VoxSiteDncRepository $repository;

    public function __construct(VoxSiteDncRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string|null $siteId Фильтр по site_id (опционально), строка например 'boostra'
     * @return array
     */
    public function getList(?string $siteId = null): array
    {
        $rows = $this->repository->findAll($siteId);
        return array_map(function (object $row): array {
            return VoxSiteDncDto::fromRow($row)->toArray();
        }, $rows);
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $row = $this->repository->findById($id);
        return $row !== null ? VoxSiteDncDto::fromRow($row)->toArray() : null;
    }

    /**
     * @param array $data
     * @return int id созданной записи
     * @throws Exception
     */
    public function create(array $data): int
    {
        $this->validateRequired($data, ['site_id', 'organization_id']);
        if ($this->repository->existsPair((string)$data['site_id'], (int)$data['organization_id'], null)) {
            throw new Exception('Запись с такой парой (сайт, организация) уже существует');
        }
        return $this->repository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function update(int $id, array $data): bool
    {
        $existing = $this->repository->findById($id);
        if ($existing === null) {
            throw new Exception('Запись не найдена');
        }
        if (isset($data['site_id'], $data['organization_id'])
            && $this->repository->existsPair((string)$data['site_id'], (int)$data['organization_id'], $id)) {
            throw new Exception('Запись с такой парой (сайт, организация) уже существует');
        }
        return $this->repository->update($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        $existing = $this->repository->findById($id);
        if ($existing === null) {
            throw new Exception('Запись не найдена');
        }
        return $this->repository->delete($id);
    }

    /**
     * @param array $data
     * @param array $keys
     * @return void
     * @throws Exception
     */
    private function validateRequired(array $data, array $keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data) || (empty($data[$key]) && $data[$key] !== 0)) {
                throw new Exception("Обязательное поле: {$key}");
            }
        }
    }
}
