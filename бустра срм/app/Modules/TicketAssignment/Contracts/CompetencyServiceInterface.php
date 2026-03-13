<?php

namespace App\Modules\TicketAssignment\Contracts;

interface CompetencyServiceInterface
{
    /**
     * Получить уровень компетенции менеджера для определенного типа тикетов
     *
     * @param int $managerId ID менеджера
     * @param string $type Тип тикета ('collection' или 'additional_services')
     * @return string|null Уровень компетенции или null если не найден
     */
    public function getManagerCompetency(int $managerId, string $type): ?string;

    /**
     * Получить список менеджеров с указанным уровнем компетенции
     *
     * @param string $type Тип тикета
     * @param string $level Уровень компетенции
     * @return array Массив ID менеджеров
     */
    public function getManagersByLevel(string $type, string $level): array;

    /**
     * Установить уровень компетенции для менеджера
     *
     * @param int $managerId ID менеджера
     * @param string $type Тип тикета
     * @param string $level Уровень компетенции
     * @return bool Результат операции
     */
    public function setManagerCompetency(int $managerId, string $type, string $level): bool;

    /**
     * Удалить уровень компетенции менеджера
     *
     * @param int $managerId ID менеджера
     * @param string $type Тип тикета
     * @return bool Результат операции
     */
    public function removeManagerCompetency(int $managerId, string $type): bool;
}