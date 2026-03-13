<?php

namespace App\Modules\Clients\Domain\Entity;

/**
 * Доменная сущность активного займа.
 * 
 * Агрегирует данные о заявке, договоре и балансе из разных таблиц БД.
 * Представляет активный займ клиента со статусом "5.Выдан".
 * 
 * @package App\Modules\Clients\Domain\Entity
 */
class ActiveLoan
{
    private array $orderData;
    private ?array $contractData;
    private ?array $balanceData;

    private array $loans;

    /**
     * @param array $orderData Данные заявки из s_orders
     * @param array|null $contractData Данные договора из s_contracts
     * @param array|null $balanceData Данные баланса из s_user_balance
     */
    public function __construct(
        array $orderData,
        ?array $contractData = null,
        ?array $balanceData = null
    ) {
        $this->orderData = $orderData;
        $this->contractData = $contractData;
        $this->balanceData = $balanceData;

        $this->loans = [
            [
                'order' => $orderData,
                'contract' => $contractData,
                'balance' => $balanceData,
            ]
        ];
    }

    /**
     * @return array Данные заявки
     */
    public function getOrderData(): array
    {
        return $this->orderData;
    }

    /**
     * @return array|null Данные договора или null если не найден
     */
    public function getContractData(): ?array
    {
        return $this->contractData;
    }

    public function getBalanceData(): ?array
    {
        return $this->balanceData;
    }

    /**
     * Возвращает все займы как массив.
     *
     * @return array<int, array{order: array, contract: ?array, balance: ?array}>
     */
    public function getLoans(): array
    {
        return $this->loans;
    }
    
    public static function fromDatabaseRow(array $row): self
    {
        $orderData = [
            'order_id' => $row['order_id'],
            'order_amount' => $row['order_amount'],
            'approve_amount' => $row['approve_amount'],
            'loan_type' => $row['order_loan_type'] ?? null,
            '1c_status' => $row['1c_status'],
            'order_date' => $row['order_date'],
            'percent' => $row['order_percent'] ?? null,
            'is_active' => (bool)($row['is_active'] ?? false)
        ];

        $contractData = null;
        if (!empty($row['contract_id'])) {
            $contractData = [
                'contract_id' => $row['contract_id'],
                'contract_db_id' => $row['contract_db_id'],
                'contract_number' => $row['contract_number'],
                'contract_amount' => $row['contract_amount'],
                'period' => $row['period'],
                'return_date' => $row['return_date'],
                'issuance_date' => $row['issuance_date'],
                'close_date' => $row['close_date'],
                'responsible_person_id' => $row['responsible_person_id'],
                'additional_service_repayment' => $row['additional_service_repayment'],
                'deleteKD' => $row['deleteKD'],
                'percent' => $row['contract_percent'] ?? null
            ];
        }

        $balanceData = null;
        if (!empty($row['zaim_date'])) {
            $balanceData = [
                'zaim_summ' => $row['zaim_summ'],
                'ostatok_od' => $row['ostatok_od'],
                'zaim_date' => $row['zaim_date'],
                'ostatok_percents' => $row['ostatok_percents'],
                'ostatok_peni' => $row['ostatok_peni'],
                'prolongation_count' => $row['prolongation_count'],
                'payment_date' => $row['payment_date'],
                'penalty' => $row['penalty'],
                'loan_type' => $row['loan_type'],
            ];
        }

        return new self($orderData, $contractData, $balanceData);
    }

    /**
     * Создает ActiveLoan из множества строк с займами.
     *
     * @param array<int, array> $rows
     * @return self
     * @throws InvalidArgumentException если массив пуст
     */
    public static function fromDatabaseRows(array $rows): self
    {
        if (empty($rows)) {
            throw new InvalidArgumentException('Rows must not be empty');
        }

        $firstRow = $rows[0];
        $instance = self::fromDatabaseRow($firstRow);

        $loans = [];
        foreach ($rows as $r) {
            $row = $r;

            $orderData = [
                'order_id' => $row['order_id'] ?? null,
                'order_amount' => $row['order_amount'] ?? null,
                'approve_amount' => $row['approve_amount'] ?? null,
                'loan_type' => $row['order_loan_type'] ?? null,
                '1c_status' => $row['1c_status'] ?? null,
                'order_date' => $row['order_date'] ?? null,
                'percent' => $row['order_percent'] ?? null,
                'is_active' => (bool)($row['is_active'] ?? false),
            ];

            $contractData = null;
            if (!empty($row['contract_id'])) {
                $contractData = [
                    'contract_id' => $row['contract_id'] ?? null,
                    'contract_db_id' => $row['contract_db_id'] ?? null,
                    'contract_number' => $row['contract_number'] ?? null,
                    'contract_amount' => $row['contract_amount'] ?? null,
                    'period' => $row['period'] ?? null,
                    'return_date' => $row['return_date'] ?? null,
                    'issuance_date' => $row['issuance_date'] ?? null,
                    'close_date' => $row['close_date'] ?? null,
                    'responsible_person_id' => $row['responsible_person_id'] ?? null,
                    'additional_service_repayment' => $row['additional_service_repayment'] ?? null,
                    'deleteKD' => $row['deleteKD'] ?? null,
                    'percent' => $row['contract_percent'] ?? null,
                ];
            }

            $balanceData = null;
            if (!empty($row['zaim_date'])) {
                $balanceData = [
                    'zaim_summ' => $row['zaim_summ'] ?? null,
                    'ostatok_od' => $row['ostatok_od'] ?? null,
                    'zaim_date' => $row['zaim_date'] ?? null,
                    'ostatok_percents' => $row['ostatok_percents'] ?? null,
                    'ostatok_peni' => $row['ostatok_peni'] ?? null,
                    'prolongation_count' => $row['prolongation_count'] ?? null,
                    'payment_date' => $row['payment_date'] ?? null,
                    'penalty' => $row['penalty'] ?? null,
                    'loan_type' => $row['loan_type'] ?? null,
                ];
            }

            $loans[] = [
                'order' => $orderData,
                'contract' => $contractData,
                'balance' => $balanceData,
            ];
        }

        $instance->loans = $loans;

        return $instance;
    }

    /**
     * Создаёт активный займ из массива с ключами order, contract, balance.
     *
     * @param array{order: array, contract: ?array, balance: ?array} $loan
     * @return self
     */
    public static function createFromArrays(array $loan): self
    {
        return new self(
            $loan['order'] ?? [],
            $loan['contract'] ?? null,
            $loan['balance'] ?? null
        );
    }

    public function hasContract(): bool
    {
        return $this->contractData !== null;
    }

    public function hasBalance(): bool
    {
        return $this->balanceData !== null;
    }

    public function getOrderId(): string
    {
        return $this->orderData['order_id'];
    }

    public function getContractNumber(): ?string
    {
        return $this->contractData['contract_number'] ?? null;
    }
}
