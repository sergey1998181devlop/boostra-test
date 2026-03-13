<?php

namespace App\Modules\VoxCallsArchive\Domain\Repository;

use App\Modules\VoxCallsArchive\Application\DTO\VoxCallDTO;

/**
 * Interface VoxCallArchiveRepositoryInterface
 * Интерфейс репозитория для работы с архивом звонков Voximplant
 */
interface VoxCallArchiveRepositoryInterface
{
    /**
     * Сохранить звонок в активную таблицу архива
     *
     * @param VoxCallDTO $dto
     * @return int|null ID вставленной записи
     */
    public function save(VoxCallDTO $dto): ?int;

    /**
     * Обновить метаданные звонка
     *
     * @param int $voxCallId ID звонка в Voximplant
     * @param array $data Данные для обновления
     * @return bool
     */
    public function updateByVoxCallId(int $voxCallId, array $data): bool;

    /**
     * Проверить существование звонка по ID Voximplant
     *
     * @param int $voxCallId
     * @return bool
     */
    public function existsByVoxCallId(int $voxCallId): bool;

    /**
     * Получить звонки по фильтру из активной таблицы
     *
     * @param array $filter
     * @return array
     */
    public function getCalls(array $filter): array;

    /**
     * Получить звонки с учетом архивных таблиц за указанный период
     *
     * @param string $dateFrom Начало периода (Y-m-d или Y-m-d H:i:s)
     * @param string $dateTo Конец периода (Y-m-d или Y-m-d H:i:s)
     * @param array $additionalFilter Дополнительные условия фильтрации
     * @return array
     */
    public function getCallsForPeriod(string $dateFrom, string $dateTo, array $additionalFilter = []): array;

    /**
     * Получить имя активной таблицы
     *
     * @return string
     */
    public function getActiveTableName(): string;

    /**
     * Получить список архивных таблиц за период
     *
     * @param string $yearMonthFrom В формате YYYY-MM
     * @param string $yearMonthTo В формате YYYY-MM
     * @return array
     */
    public function getArchiveTablesForPeriod(string $yearMonthFrom, string $yearMonthTo): array;
}
