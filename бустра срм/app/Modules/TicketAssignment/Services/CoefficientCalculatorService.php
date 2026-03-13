<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\TicketAssignment\Enums\CompetencyLevel;

/**
 * Сервис для расчета коэффициентов нагрузки тикетов
 */
class CoefficientCalculatorService
{
    /** @var float Коэффициент для просрочки 1-7 дней */
    private const COEFFICIENT_SOFT = 1.0;

    /** @var float Коэффициент для просрочки 8-30 дней */
    private const COEFFICIENT_MIDDLE = 1.5;

    /** @var float Коэффициент для просрочки >30 дней */
    private const COEFFICIENT_HARD = 2.0;

    /** @var float Дополнительный коэффициент для высокого приоритета */
    private const COEFFICIENT_HIGH_PRIORITY = 1.2;

    /** @var int ID высокого приоритета в системе */
    private const HIGH_PRIORITY_ID = 3;

    /**
     * Рассчитать базовый коэффициент по дням просрочки
     *
     * @param int|null $overdueDays
     * @return float
     */
    public function getBaseCoefficient(?int $overdueDays): float
    {
        if ($overdueDays === null) {
            return self::COEFFICIENT_SOFT;
        }

        $level = CompetencyLevel::getByOverdueDays($overdueDays);

        switch ($level) {
            case CompetencyLevel::MIDDLE:
                return self::COEFFICIENT_MIDDLE;
            case CompetencyLevel::HARD:
                return self::COEFFICIENT_HARD;
            default:
                return self::COEFFICIENT_SOFT;
        }
    }

    /**
     * Получить коэффициент приоритета
     *
     * @param int $priorityId
     * @return float
     */
    public function getPriorityCoefficient(int $priorityId): float
    {
        return $priorityId === self::HIGH_PRIORITY_ID ? self::COEFFICIENT_HIGH_PRIORITY : 1.0;
    }

    /**
     * Рассчитать итоговый коэффициент для тикета
     *
     * @param int|null $overdueDays Дни просрочки
     * @param int $priorityId ID приоритета тикета
     * @return float Итоговый коэффициент
     */
    public function calculateTotalCoefficient(?int $overdueDays, int $priorityId): float
    {
        $baseCoefficient = $this->getBaseCoefficient($overdueDays);
        $priorityCoefficient = $this->getPriorityCoefficient($priorityId);

        return round($baseCoefficient * $priorityCoefficient, 2);
    }

}
