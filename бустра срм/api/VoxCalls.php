<?php

require_once 'Simpla.php';

use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveService;
use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveQueryService;

class VoxCalls extends Simpla
{
    /** @var VoxCallsArchiveService|null */
    private $archiveService = null;

    /** @var VoxCallsArchiveQueryService|null */
    private $queryService = null;

    /**
     * Получить сервис записи в архив
     *
     * @return VoxCallsArchiveService
     */
    private function getArchiveService(): VoxCallsArchiveService
    {
        if ($this->archiveService === null) {
            $this->archiveService = new VoxCallsArchiveService();
        }
        return $this->archiveService;
    }

    /**
     * Получить сервис чтения из архива
     *
     * @return VoxCallsArchiveQueryService
     */
    private function getQueryService(): VoxCallsArchiveQueryService
    {
        if ($this->queryService === null) {
            $this->queryService = new VoxCallsArchiveQueryService();
        }
        return $this->queryService;
    }

    /**
     * Сохранить звонок в архивную БД
     *
     * @param stdClass $call
     * @return int|null ID вставленной записи
     */
    public function save(stdClass $call)
    {
        $call->created = date('Y-m-d H:i:s');

        $phoneA = formatPhoneNumber($call->phone_a);
        $phoneB = formatPhoneNumber($call->phone_b);
        if ($phoneA !== false) {
            $call->phone_a = $phoneA;
        }
        if ($phoneB !== false) {
            $call->phone_b = $phoneB;
        }

        $user = $this->users->getUsersByPhoneNumbers([$call->phone_a, $call->phone_b]);
        $user_id = !empty($user) ? $user->id : null;
        $call->user_id_internal = $user_id;

        // Запись ТОЛЬКО в архивную БД
        $archiveService = $this->getArchiveService();
        $id = $archiveService->saveFromLegacy($call);

        return $id;
    }

    /**
     * Получить звонки по фильтру
     *
     * @param array $filter
     * @return array
     */
    public function get_calls($filter)
    {
        $archiveService = $this->getArchiveService();
        $results = $archiveService->getCalls($filter);

        // Преобразуем результаты в объекты для совместимости
        return array_map(function ($row) {
            return (object)$row;
        }, $results);
    }

    /**
     * Создает пустую запись в s_vox_robot_calls с нужным типом
     * (Эта функция остаётся без изменений - работает с основной БД)
     *
     * @param object $user
     * @param string $type
     */
    public function setNewCall($user, $type)
    {
        $this->db->query("
                INSERT INTO s_vox_robot_calls
                (user_id, client_phone, vox_call_id, status, is_redirected_manager, type, created_at, updated_at)
                VALUES (?, ?, 0, 0, false, ?, NOW(), NOW())",
            $user->id,
            $user->phone_mobile,
            $type
        );
    }

    /**
     * Проверить существование звонка по ID Voximplant
     *
     * @param int $voxCallId
     * @return bool
     */
    public function existsByVoxCallId(int $voxCallId): bool
    {
        $archiveService = $this->getArchiveService();
        return $archiveService->existsByVoxCallId($voxCallId);
    }

    /**
     * Обновить метаданные звонка (для отчётов)
     *
     * @param array $call
     * @return void
     */
    public function updateReportMeta(array $call): void
    {
        if (empty($call['id']) || empty($call['datetime_start'])) {
            return;
        }

        $archiveService = $this->getArchiveService();
        $archiveService->updateReportMeta($call);
    }
}
