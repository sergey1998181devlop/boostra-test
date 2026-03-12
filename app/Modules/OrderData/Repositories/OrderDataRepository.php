<?php

namespace App\Modules\OrderData\Repositories;

use App\Core\Helpers\Collection;
use App\Core\Repositories\BaseRepository;
use OrderData;
use stdClass;

class OrderDataRepository extends BaseRepository
{
    /**
     * Get table name
     *
     * @return string
     */
    protected function getTable(): string
    {
        return '__order_data';
    }

    public function getAdditionalDataFields(int $orderId): Collection
    {
        $result = $this->query(
            "SELECT * FROM {$this->model->table} WHERE `order_id` = ? and `key` in (?@)",
            $orderId,
            OrderData::ADDITIONAL_SERVICES
        );

        return collect($result);
    }
}