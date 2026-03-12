<?php

namespace App\Repositories;

use App\Core\Models\BaseModel;

class DoctorConditionRepository
{
    private BaseModel $model;

    public function __construct()
    {
        $this->model = new BaseModel();
        $this->model->table = '__credit_doctor_conditions';
    }

    /**
     * Получить тариф по сумме и статусу нового клиента.
     */
    public function getCreditDoctor(int $amount, bool $isNewClient = true): ?object
    {
        $this->model
            ->query(
                "SELECT id, price
                   FROM {$this->model->table}
                  WHERE is_new      = ?
                    AND from_amount <= ?
                    AND to_amount   >= ?
                  LIMIT 1",
                $isNewClient ? 1 : 0,
                $amount - 1,
                $amount - 1
            )
            ->result();

        return $this->model->getData();
    }

    /**
     * Получить тариф по сумме и группам цен (price_group).
     */
    public function getCreditDoctorByPriceGroup(int $amount, string $priceGroup): ?object
    {
        $this->model
            ->query(
                "SELECT id, price
                   FROM {$this->model->table}
                  WHERE price_group = ?
                    AND from_amount  <= ?
                    AND to_amount    >= ?
                  LIMIT 1",
                $priceGroup,
                $amount - 1,
                $amount - 1
            )
            ->result();

        $result = $this->model->getData();

        return !is_object($result) ? null : $result;
    }
}
