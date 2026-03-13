<?php

namespace App\Modules\VoxCallsArchive\Application\Service;

use App\Modules\VoxCallsArchive\Application\DTO\VoxCallDTO;
use App\Modules\VoxCallsArchive\Infrastructure\Repository\VoxCallArchiveRepository;

/**
 * Class VoxCallsArchiveService
 * Сервис для записи звонков в архивную базу данных
 */
class VoxCallsArchiveService
{
    /** @var int Минимальная длительность звонка для сохранения (в секундах) */
    private const MIN_DURATION_SECONDS = 10;

    /** @var VoxCallArchiveRepository */
    private $repository;

    /** @var VoxCallArchiveRepository|null */
    private static $repositoryInstance = null;

    public function __construct(VoxCallArchiveRepository $repository = null)
    {
        if ($repository !== null) {
            $this->repository = $repository;
        } else {
            if (self::$repositoryInstance === null) {
                self::$repositoryInstance = new VoxCallArchiveRepository();
            }
            $this->repository = self::$repositoryInstance;
        }
    }

    /**
     * Сохранить звонок из легаси формата (stdClass)
     *
     * @param \stdClass $call
     * @return int|null ID вставленной записи или null если звонок слишком короткий
     */
    public function saveFromLegacy(\stdClass $call): ?int
    {
        $dto = VoxCallDTO::fromLegacy($call);

        if (!$this->isValidDuration($dto)) {
            return null;
        }

        return $this->repository->save($dto);
    }

    /**
     * Сохранить звонок из массива
     *
     * @param array $data
     * @return int|null ID вставленной записи или null если звонок слишком короткий
     */
    public function saveFromArray(array $data): ?int
    {
        $dto = VoxCallDTO::fromArray($data);

        if (!$this->isValidDuration($dto)) {
            return null;
        }

        return $this->repository->save($dto);
    }

    /**
     * Сохранить звонок из DTO
     *
     * @param VoxCallDTO $dto
     * @return int|null ID вставленной записи или null если звонок слишком короткий
     */
    public function save(VoxCallDTO $dto): ?int
    {
        if (!$this->isValidDuration($dto)) {
            return null;
        }

        return $this->repository->save($dto);
    }

    /**
     * Проверить, что длительность звонка достаточна для сохранения
     *
     * @param VoxCallDTO $dto
     * @return bool
     */
    private function isValidDuration(VoxCallDTO $dto): bool
    {
        return $dto->duration !== null && $dto->duration >= self::MIN_DURATION_SECONDS;
    }

    /**
     * Обновить метаданные отчета звонка
     *
     * @param array $call Данные звонка (должен содержать 'id' - vox_call_id)
     * @return void
     */
    public function updateReportMeta(array $call): void
    {
        if (empty($call['id'])) {
            return;
        }

        $voxCallId = (int)$call['id'];

        $updateData = [];

        if (isset($call['queue_id'])) {
            $updateData['queue_id'] = (int)$call['queue_id'];
        }

        if (isset($call['user_id'])) {
            $updateData['vox_user_id'] = (int)$call['user_id'];
        }

        if (isset($call['record_url'])) {
            $updateData['record_url'] = $call['record_url'];
        }

        if (array_key_exists('assessment', $call) && $call['assessment'] !== null && $call['assessment'] !== '') {
            $updateData['assessment'] = (int)$call['assessment'];
        }

        if (!empty($updateData)) {
            $this->repository->updateByVoxCallId($voxCallId, $updateData);
        }
    }

    /**
     * Проверить существование звонка по ID Voximplant
     *
     * @param int $voxCallId
     * @return bool
     */
    public function existsByVoxCallId(int $voxCallId): bool
    {
        return $this->repository->existsByVoxCallId($voxCallId);
    }

    /**
     * Получить звонки по фильтру
     *
     * @param array $filter
     * @return array
     */
    public function getCalls(array $filter): array
    {
        return $this->repository->getCalls($filter);
    }

    /**
     * Получить репозиторий
     *
     * @return VoxCallArchiveRepository
     */
    public function getRepository(): VoxCallArchiveRepository
    {
        return $this->repository;
    }
}
