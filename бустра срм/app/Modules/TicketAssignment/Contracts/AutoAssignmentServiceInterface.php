<?php

namespace App\Modules\TicketAssignment\Contracts;

interface AutoAssignmentServiceInterface
{
    /**
     * Назначить все неназначенные тикеты
     *
     * @return array Результат операции с количеством назначенных и ошибок
     */
    public function assignUnassignedTickets(): array;

    /**
     * Назначить тикет на менеджера
     *
     * @param object $ticket Объект тикета
     * @param int $managerId ID менеджера
     * @return array{success: bool, message?: string} Результат назначения
     */
    public function assignTicketToManager(object $ticket, int $managerId): array;
}
