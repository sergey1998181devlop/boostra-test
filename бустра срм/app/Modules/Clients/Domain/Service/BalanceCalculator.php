<?php

namespace App\Modules\Clients\Domain\Service;

use App\Modules\Clients\Application\DTO\BalanceDTO;
use App\Modules\Clients\Domain\Entity\ActiveLoan;
use App\Modules\Clients\Domain\Entity\Client;
use Carbon\Carbon;

/**
 * Доменный сервис для расчета баланса клиента.
 *
 * Реализует бизнес-логику расчета баланса с приоритетами источников данных:
 * 1. s_user_balance (если актуальные для контракта)
 * 2. loan_history (резервный источник)
 * 3. Пустые значения по умолчанию
 *
 * @package App\Modules\Clients\Domain\Service
 */
class BalanceCalculator
{
    private LoanHistoryMatcher $loanHistoryMatcher;

    public function __construct(LoanHistoryMatcher $loanHistoryMatcher)
    {
        $this->loanHistoryMatcher = $loanHistoryMatcher;
    }
    /**
     * Рассчитывает баланс клиента по активному займу.
     *
     * Использует приоритетную логику: проверяет актуальность данных из s_user_balance,
     * в противном случае использует loan_history или возвращает пустой баланс.
     *
     * @param Client $client Клиент с историей займов
     * @param ActiveLoan $activeLoan Активный займ для расчета
     * @return BalanceDTO Рассчитанный баланс
     */
    public function calculateBalance(Client $client, ActiveLoan $activeLoan): BalanceDTO
    {
        $balanceData = $activeLoan->getBalanceData();
        $contractData = $activeLoan->getContractData();

        // ПРИОРИТЕТ 1: Сначала проверяем актуальность s_user_balance
        $isDbBalanceRelevant = $this->isBalanceRelevantForContract($contractData, $balanceData);
        if ($activeLoan->hasBalance() && $isDbBalanceRelevant) {
            return $this->calculateFromDatabase($balanceData, $activeLoan);
        }

        // ПРИОРИТЕТ 2: Резервный сценарий - loan_history
        $matchingLoan = $this->findRelevantLoanHistoryEntry($client, $activeLoan);
        if ($matchingLoan) {
            return $this->calculateFromLoanHistory($matchingLoan);
        }

        // ПРИОРИТЕТ 3: По умолчанию
        return BalanceDTO::createEmpty();
    }

    /**
     * Проверяет, являются ли данные из s_user_balance актуальными для текущего контракта.
     */
    public function isBalanceRelevantForContract(?array $contractData, ?array $balanceData): bool
    {
        if (!$balanceData || !$contractData) {
            return false;
        }

        $contractIssuanceDate = Carbon::parse($contractData['issuance_date'] ?? '1970-01-01')->startOfDay();
        $balanceZaimDate = Carbon::parse($balanceData['zaim_date'] ?? '1970-01-01')->startOfDay();

        if (!$contractIssuanceDate->eq($balanceZaimDate)) {
            return false;
        }

        $paymentDate = Carbon::parse($balanceData['payment_date'] ?? '1970-01-01')->startOfDay();

        if ($paymentDate->lt($contractIssuanceDate)) {
            return false;
        }

        $today = Carbon::today();

        if ($contractIssuanceDate->eq($today) && $paymentDate->lt($today)) {
            return false;
        }

        return true;
    }

    /**
     * Рассчитывает баланс на основе данных из s_user_balance.
     *
     * Включает fallback логику: если долг равен 0, использует сумму договора.
     *
     * @param array $balanceData Данные баланса из s_user_balance
     * @param ActiveLoan $activeLoan Активный займ для получения данных заказа/контракта
     * @return BalanceDTO Рассчитанный баланс
     */
    public function calculateFromDatabase(array $balanceData, ActiveLoan $activeLoan): BalanceDTO
    {
        $orderData = $activeLoan->getOrderData();
        $contractData = $activeLoan->getContractData();

        $clearDebt = (float)($balanceData['ostatok_od'] ?? 0);
        $percent = (float)($balanceData['ostatok_percents'] ?? 0);
        $debt = $clearDebt + $percent;

        // Если долг равен 0, используем сумму договора как fallback
        if ($debt == 0 && $contractData && !empty($contractData['contract_amount'])) {
            $clearDebt = (float)$contractData['contract_amount'];
            $debt = $clearDebt;
        }

        return new BalanceDTO(
            $percent,
            (float)($balanceData['ostatok_peni'] ?? 0),
            $clearDebt,
            round($debt, 2),
            !($orderData['deleteKD'] ?? true) && ($balanceData['penalty'] ?? 0) > 0
        );
    }

    /**
     * Рассчитывает баланс на основе данных из loan_history.
     *
     * Используется как резервный источник данных когда s_user_balance не актуален.
     *
     * @param array $loanHistoryEntry Запись из loan_history поля s_users
     * @return BalanceDTO Рассчитанный баланс
     */
    public function calculateFromLoanHistory(array $loanHistoryEntry): BalanceDTO
    {
        $clearDebt = (float)($loanHistoryEntry['loan_body_summ'] ?? $loanHistoryEntry['amount'] ?? 0);
        $percents = (float)($loanHistoryEntry['loan_percents_summ'] ?? 0);
        $debt = $clearDebt + $percents;

        return new BalanceDTO(
            $percents,
            0.0,
            $clearDebt,
            $debt,
            false
        );
    }

    /**
     * Находит релевантную запись в `loan_history` для активного займа.
     *
     * @param Client $client Клиент с историей займов
     * @param ActiveLoan $activeLoan Активный займ для поиска
     * @return array|null Найденная запись из loan_history или null
     */
    public function findRelevantLoanHistoryEntry(Client $client, ActiveLoan $activeLoan): ?array
    {
        return $this->loanHistoryMatcher->findMatchingLoanHistory($client, $activeLoan);
    }
}
