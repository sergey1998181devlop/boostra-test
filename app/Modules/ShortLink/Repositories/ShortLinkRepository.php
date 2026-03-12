<?php

namespace App\Modules\ShortLink\Repositories;

use App\Core\Repositories\BaseRepository;

/**
 * Репозиторий для работы с короткими ссылками
 */
class ShortLinkRepository extends BaseRepository
{
    /**
     * Get table name
     *
     * @return string
     */
    protected function getTable(): string
    {
        return '__short_link';
    }

    /**
     * Получить данные по короткой ссылке с контрактом и займом одним оптимизированным запросом
     *
     * @param string $code
     * @return object|null
     */
    public function getLinkData(string $code): ?object
    {
        return $this->queryFirst(
            "SELECT 
                sl.id as short_link_id,
                sl.user_id,
                sl.type,
                sl.zaim_number,
                sl.order_id,
                c.id as contract_id,
                c.number as contract_number,
                o.1c_status AS status_1c
            FROM {$this->model->table} sl
            LEFT JOIN __contracts c ON c.number COLLATE utf8mb3_general_ci = sl.zaim_number COLLATE utf8mb3_general_ci
            LEFT JOIN __orders o ON o.id = c.order_id
            WHERE sl.link = ?
            GROUP BY sl.id, sl.user_id, sl.type, sl.zaim_number, sl.order_id,
                     c.id, c.number, o.id, o.1c_status, o.status",
            $code
        );
    }
}
