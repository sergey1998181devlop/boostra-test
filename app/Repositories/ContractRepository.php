<?php

namespace App\Repositories;

use App\Core\Models\BaseModel;

class ContractRepository
{
    private $model;

    public function __construct()
    {
        $this->model = new BaseModel();
        $this->model->table = '__contracts';
    }

    /**
     * Получить первый контракт клиента.
     */
    public function getFirstContractByUserId(int $userId)
    {
        return $this->model
            ->query(
                "SELECT id, order_id, issuance_date FROM {$this->model->table} WHERE user_id = ? ORDER BY create_date ASC LIMIT 1",
                $userId
            )
            ->result()
            ->getData();
    }
}
