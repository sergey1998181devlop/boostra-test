<?php

namespace App\Modules\TicketAssignment\Contracts;

interface ManagerFinderServiceInterface
{
    /**
     * Найти подходящего менеджера для тикета
     *
     * @param object $ticket Объект тикета
     * @return int ID менеджера
     * @throws \RuntimeException Если не найден подходящий менеджер
     */
    public function findAvailableManager(object $ticket): int;

    /**
     * Найти менеджера для эскалированного тикета
     *
     * @param object $ticket Объект тикета
     * @return int ID менеджера
     * @throws \RuntimeException Если не найден подходящий менеджер
     */
    public function findEscalationManager(object $ticket): int;

    /**
     * Очистить кэш менеджеров эскалации
     */
    public function clearEscalationManagersCache(): void;
}
