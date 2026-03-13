<?php

namespace App\Modules\Clients\Domain\Service;

use App\Modules\Clients\Domain\Entity\ActiveLoan;
use App\Modules\Clients\Domain\Entity\Client;

/**
 * Доменный сервис для поиска соответствий между активными займами и записями в loan_history.
 * 
 * @package App\Modules\Clients\Domain\Service
 */
class LoanHistoryMatcher
{
    /**
     * Находит запись в loan_history, соответствующую активному займу.
     * 
     * Использует приоритетную логику сопоставления:
     * 1. По полному совпадению номера контракта
     * 2. По order_id в конце номера займа (regex)
     * 
     * @param Client $client Клиент с историей займов
     * @param ActiveLoan $activeLoan Активный займ для поиска
     * @return array|null Найденная запись из loan_history или null
     */
    public function findMatchingLoanHistory(Client $client, ActiveLoan $activeLoan): ?array
    {
        $loanHistory = $client->getLoanHistory();
        
        if (empty($loanHistory)) {
            return null;
        }

        $orderData = $activeLoan->getOrderData();
        $contractData = $activeLoan->getContractData();

        foreach ($loanHistory as $loan) {
            // Пропускаем закрытые займы
            if (!empty($loan['close_date'])) {
                continue;
            }

            if ($this->isMatchingLoan($loan, $orderData, $contractData)) {
                return $loan;
            }
        }

        return null;
    }

    /**
     * Проверяет соответствие записи из loan_history активному займу.
     * 
     * Логика сопоставления:
     * - Прямое совпадение по номеру контракта
     * - Совпадение по order_id в конце номера займа
     * 
     * @param array $historyLoan Запись займа из loan_history
     * @param array $orderData Данные заявки из активного займа
     * @param array|null $contractData Данные контракта из активного займа
     * @return bool true если займ соответствует активному займу
     */
    private function isMatchingLoan(array $historyLoan, array $orderData, ?array $contractData): bool
    {
        $contractNumber = $contractData['contract_number'] ?? '';
        $orderId = $orderData['order_id'] ?? '';
        
        // Проверяем по номеру контракта
        if (!empty($contractNumber) && !empty($historyLoan['number'])) {
            if ($historyLoan['number'] === $contractNumber) {
                return true;
            }
        }

        // Проверяем по order_id в конце номера
        if (!empty($historyLoan['number']) && !empty($orderId)) {
            if (preg_match('/(\d+)$/', $historyLoan['number'], $matches)) {
                if ($matches[1] == $orderId) {
                    return true;
                }
            }
        }

        return false;
    }
}