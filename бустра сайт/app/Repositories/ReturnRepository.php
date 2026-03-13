<?php

namespace App\Repositories;

use App\Core\Models\BaseModel;
use DateTime;
use Exception;

class ReturnRepository
{
    /** @var BaseModel */
    private BaseModel $model;

    public function __construct()
    {
        $this->model = new BaseModel();
    }

    /**
     * Количество пролонгаций по последнему одобренному займу пользователя.
     */
    public function getProlongationCount(int $userId): int
    {
        $orderId = $this->getLastOrderIdByUserId($userId);
        if (!$orderId) {
            return 0;
        }

        $this->model->query(
            "SELECT COUNT(*) AS cnt
               FROM b2p_payments
              WHERE prolongation = 1 
                AND order_id = ?",
            $orderId
        )->result();

        $record = $this->model->getData();

        return $record ? (int) $record->cnt : 0;
    }

    /**
     * ID последнего одобренного займа пользователя.
     *  WARNING: order_id берём из p2pcredits, чтобы обойти баг статусов в orders.
     *  Статус этого заказа в orders может отличаться от фактического состояния, отражённого в p2pcredits.
     */
    public function getLastOrderIdByUserId(int $userId): ?int
    {
        $this->model->query(
            "SELECT order_id 
               FROM b2p_p2pcredits
              WHERE status = 'APPROVED'
                AND user_id = ?
           ORDER BY date DESC 
              LIMIT 1",
            $userId
        )->result();

        $record = $this->model->getData();

        if (!$record || empty($record->order_id)) {
            return null;
        }

        return (int) $record->order_id;
    }

    /**
     * Проверить, просрочен ли последний одобренный займ пользователя.
     *
     * Логика (часы не считаем, только даты):
     * - плановая дата = date(issuance_date) + period дней
     * - если return_date не null → сравниваем date(return_date) с плановой
     * - если return_date null → сравниваем сегодняшнюю дату с плановой
     *
     * @throws Exception
     */
    public function checkIsLastOrderOverdue(int $userId): bool
    {
        $orderId = $this->getLastOrderIdByUserId($userId);
        if (!$orderId) {
            return false;
        }

        $this->model->query(
            "SELECT period, issuance_date, return_date
               FROM __contracts
              WHERE order_id = ?
           ORDER BY id DESC 
              LIMIT 1",
            $orderId,
        )->result();

        $contract = $this->model->getData();

        if (
            !$contract
            || empty($contract->issuance_date)
            || $contract->period === null
        ) {
            return false;
        }

        $period = (int) $contract->period;

        // Дата выдачи (обрубаем время)
        $issuanceDate = new DateTime($contract->issuance_date);
        $issuanceDate->setTime(0, 0, 0);

        // Плановая дата возврата
        $plannedReturnDate = (clone $issuanceDate)->modify("+{$period} days");

        // Если займ уже возвращён — сравниваем с датой возврата
        if (!empty($contract->return_date)) {
            $returnDate = new DateTime($contract->return_date);
            $returnDate->setTime(0, 0, 0);

            return $returnDate > $plannedReturnDate;
        }

        // Если ещё не возвращён — смотрим на сегодняшнюю дату
        $today = new DateTime('today'); // 00:00:00

        return $today > $plannedReturnDate;
    }
}
