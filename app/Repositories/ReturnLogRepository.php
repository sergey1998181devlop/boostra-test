<?php
namespace App\Repositories;

use App\Core\Models\BaseModel;
use Medoo\Medoo;

class ReturnLogRepository
{
    private BaseModel $model;
    private string    $status;

    public function __construct(string $tableName, string $status)
    {
        $this->model  = new BaseModel();
        $this->model->table = $tableName;
        $this->status = $status;
    }

    /**
     * Сколько возвратов за $days дней у $userId.
     */
    public function countByUser(int $userId, int $days): int
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $this->model
            ->query(
                "SELECT COUNT(*) AS cnt
                   FROM {$this->model->table}
                  WHERE user_id       = ?
                    AND status        = ?
                    AND return_date  >= ?
                    AND return_date IS NOT NULL",
                $userId,
                $this->status,
                $date
            )
            ->result();

        $row = $this->model->getData();

        return $row && isset($row->cnt)
            ? (int)$row->cnt
            : 0;
    }
}
