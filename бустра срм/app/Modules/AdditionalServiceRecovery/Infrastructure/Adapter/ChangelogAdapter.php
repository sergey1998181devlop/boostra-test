<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter;

use Changelogs;

/**
 * Адаптер для legacy Changelogs
 */
class ChangelogAdapter
{
    /** @var Changelogs */
    private Changelogs $changelogs;

    /**
     * @param Changelogs $changelogs
     */
    public function __construct(Changelogs $changelogs)
    {
        $this->changelogs = $changelogs;
    }

    /**
     * Записывает в лог событие автоматического восстановления услуги.
     *
     * @param int $orderId
     * @param int $userId
     * @param string $serviceKey
     * @param int $managerId ID менеджера, инициировавшего процесс (может быть системным пользователем)
     * @return void
     */
    public function logServiceReEnabled(int $orderId, int $userId, string $serviceKey, int $managerId): void
    {
        $this->changelogs->add_changelog([
            'manager_id' => $managerId,
            'created' => date('Y-m-d H:i:s'),
            'type' => $serviceKey,
            'old_values' => 'Выключение',
            'new_values' => 'Включение',
            'user_id' => $userId,
            'order_id' => $orderId,
        ]);
    }

    /**
     * Записывает в лог событие изменения параметра autodebit у карты/СБП счёта
     *
     * @param int $orderId
     * @param int $userId
     * @param string $type
     * @param int $managerId ID менеджера, инициировавшего процесс (может быть системным пользователем)
     * @param string $oldData
     * @param string $newData
     * @return void
     */
    public function logAutodebitParamChange(
        int $orderId,
        int $userId,
        string $type,
        int $managerId,
        string $oldData,
        string $newData
    ): void {
        $this->changelogs->add_changelog([
            'manager_id' => $managerId,
            'created' => date('Y-m-d H:i:s'),
            'type' => $type,
            'old_values' => $oldData,
            'new_values' => $newData,
            'user_id' => $userId,
            'order_id' => $orderId,
        ]);
    }
}