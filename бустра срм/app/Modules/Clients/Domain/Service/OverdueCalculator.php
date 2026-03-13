<?php

namespace App\Modules\Clients\Domain\Service;

use App\Modules\Clients\Domain\Entity\ActiveLoan;
use App\Modules\Clients\Domain\Entity\Client;
use Carbon\Carbon;

/**
 * Доменный сервис для расчета просрочки займов.
 *
 * Реализует приоритетную логику расчета просрочки:
 * 1. overdue_date из loan_history
 * 2. payment_date из s_user_balance
 * 3. plan_close_date из loan_history
 *
 * @package App\Modules\Clients\Domain\Service
 */
class OverdueCalculator
{
    private BalanceCalculator $balanceCalculator;

    /**
     * @param BalanceCalculator $balanceCalculator Сервис для проверки актуальности баланса
     */
    public function __construct(BalanceCalculator $balanceCalculator)
    {
        $this->balanceCalculator = $balanceCalculator;
    }

    /**
     * Рассчитывает информацию о просрочке с приоритетной логикой.
     *
     * @param ActiveLoan $activeLoan Активный займ с данными
     * @param Client $client Клиент для поиска в loan_history
     * @param array|null $historyEntry Найденная запись из loan_history
     * @return array Массив с ключами 'days_overdue' и 'is_overdue'
     */
    public function calculateOverdueInfo(ActiveLoan $activeLoan, Client $client, ?array $historyEntry): array
    {
        $overdueInfo = $this->calculateFromOverdueDate($historyEntry);
        if ($overdueInfo !== null) {
            return $overdueInfo;
        }

        $overdueInfo = $this->calculateFromPaymentDate($activeLoan);
        if ($overdueInfo !== null) {
            return $overdueInfo;
        }

        $overdueInfo = $this->calculateFromPlanCloseDate($historyEntry);
        if ($overdueInfo !== null) {
            return $overdueInfo;
        }

        return $this->createOverdueResult(0);
    }

    /**
     * Рассчитывает просрочку на основе overdue_date из loan_history.
     *
     * @param array|null $historyEntry Запись из loan_history
     * @return array|null Результат расчета или null, если дата отсутствует
     */
    private function calculateFromOverdueDate(?array $historyEntry): ?array
    {
        if (empty($historyEntry['overdue_date'])) {
            return null;
        }

        $overdueDate = $this->createDateFromString($historyEntry['overdue_date']);
        $daysOverdue = $this->calculateDaysOverdue($overdueDate);

        return $this->createOverdueResult($daysOverdue);
    }

    /**
     * Рассчитывает просрочку на основе payment_date из s_user_balance.
     *
     * @param ActiveLoan $activeLoan Активный займ с данными
     * @return array|null Результат расчета или null, если данные неактуальны
     */
    private function calculateFromPaymentDate(ActiveLoan $activeLoan): ?array
    {
        $balanceData = $activeLoan->getBalanceData();
        $contractData = $activeLoan->getContractData();

        $isBalanceRelevant = $this->balanceCalculator->isBalanceRelevantForContract($contractData, $balanceData);
        if (!$isBalanceRelevant || empty($balanceData['payment_date'])) {
            return null;
        }

        $issuanceDate = $this->createDateFromString($contractData['issuance_date'] ?? '1970-01-01');
        $today = $this->createToday();

        if ($issuanceDate->eq($today)) {
            return $this->createOverdueResult(0);
        }

        $paymentDate = $this->createDateFromString($balanceData['payment_date']);
        $daysOverdue = $this->calculateDaysOverdue($paymentDate);

        return $this->createOverdueResult($daysOverdue);
    }

    /**
     * Рассчитывает просрочку на основе plan_close_date из loan_history.
     *
     * @param array|null $historyEntry Запись из loan_history
     * @return array|null Результат расчета или null, если дата отсутствует
     */
    private function calculateFromPlanCloseDate(?array $historyEntry): ?array
    {
        if (empty($historyEntry['plan_close_date'])) {
            return null;
        }

        $planCloseDate = $this->createDateFromString($historyEntry['plan_close_date']);
        $daysOverdue = $this->calculateDaysOverdue($planCloseDate);

        return $this->createOverdueResult($daysOverdue);
    }

    /**
     * Вычисляет количество дней просрочки относительно сегодняшней даты.
     *
     * @param Carbon $targetDate Целевая дата для сравнения
     * @return int Количество дней просрочки (0 если дата в будущем)
     */
    private function calculateDaysOverdue(Carbon $targetDate): int
    {
        $today = $this->createToday();
        return $targetDate->lt($today) ? $targetDate->diffInDays($today) : 0;
    }

    /**
     * Создает объект Carbon из строки с обнуленным временем.
     *
     * @param string $dateString Дата в строковом формате
     * @return Carbon Объект Carbon с временем 00:00:00
     * @throws \InvalidArgumentException Если дата имеет некорректный формат
     */
    private function createDateFromString(string $dateString): Carbon
    {
        try {
            return Carbon::parse($dateString)->startOfDay();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Некорректная дата: {$dateString}", 0, $e);
        }
    }

    /**
     * Создает объект Carbon для текущего дня с обнуленным временем.
     *
     * @return Carbon Сегодняшняя дата с временем 00:00:00
     */
    private function createToday(): Carbon
    {
        return Carbon::today();
    }

    /**
     * Создает стандартный результат расчета просрочки.
     *
     * @param int $daysOverdue Количество дней просрочки
     * @return array Массив с ключами 'days_overdue' и 'is_overdue'
     */
    private function createOverdueResult(int $daysOverdue): array
    {
        return [
            'days_overdue' => $daysOverdue,
            'is_overdue' => $daysOverdue > 0,
        ];
    }

    /**
     * Определяет эффективную дату платежа согласно приоритетной логике.
     *
     * @param array|null $balanceData Данные баланса из s_user_balance
     * @param array|null $historyEntry Запись из loan_history
     * @return Carbon|null Эффективная дата платежа или null, если все источники пусты
     */
    public function getEffectivePaymentDate(?array $balanceData, ?array $historyEntry): ?Carbon
    {
        if (!empty($historyEntry['overdue_date'])) {
            return $this->createDateFromString($historyEntry['overdue_date']);
        }

        if (!empty($balanceData['payment_date'])) {
            return $this->createDateFromString($balanceData['payment_date']);
        }

        if (!empty($historyEntry['plan_close_date'])) {
            return $this->createDateFromString($historyEntry['plan_close_date']);
        }

        return null;
    }

    /**
     * Рассчитывает дни просрочки на основе plan_close_date
     *
     * @param array|null $historyEntry Запись из loan_history
     * @return int Количество дней просрочки (0 если дата отсутствует или в будущем)
     */
    public function calculatePlanCloseOverdueDays(?array $historyEntry): int
    {
        if (empty($historyEntry['plan_close_date'])) {
            return 0;
        }
        
        $planCloseDate = $this->createDateFromString($historyEntry['plan_close_date']);
        return $this->calculateDaysOverdue($planCloseDate);
    }
}
