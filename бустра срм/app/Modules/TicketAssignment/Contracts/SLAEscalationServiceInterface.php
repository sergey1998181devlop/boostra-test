<?php

namespace App\Modules\TicketAssignment\Contracts;

interface SLAEscalationServiceInterface
{
    /**
     * Проверяет SLA нарушения и выполняет эскалацию
     *
     * @return array{checked: int, escalated: int, errors: array} Результат проверки
     */
    public function checkAndEscalateViolations(): array;

    /**
     * Эскалирует тикет на следующий уровень
     *
     * @param object $ticket Объект тикета
     * @return array{success: bool, message?: string} Результат эскалации
     */
    public function escalateTicket(object $ticket): array;

    /**
     * Получить SLA таймаут для уровня из настроек
     *
     * @param int $level Уровень SLA
     * @return int Время в часах
     */
    public function getSLATimeout(int $level): int;

    /**
     * Установить SLA дедлайн для тикета
     *
     * @param int $ticketId ID тикета
     * @param int $level Уровень SLA (по умолчанию 1)
     */
    public function setSLADeadline(int $ticketId, int $level = 1): void;

    /**
     * Проверить, является ли тикет уже эскалированным (уровень > 1)
     */
    public function isEscalated(object $ticket): bool;
}
