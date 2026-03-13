<?php

namespace App\Modules\BRReport\Repositories;

use App\Modules\BRReport\Dto\BRReportFilterDto;
use Generator;
use Simpla;

/**
 * Репозиторий для работы с данными отчета БР (Банк России)
 */
class BRReportRepository
{
    /** @var Simpla */
    private $db;

    /**
     * ID родительской темы "Тех. поддержка" - исключается из отчета
     */
    private const TECH_SUPPORT_PARENT_ID = 30;

    /**
     * ID каналов, исключаемых из отчета
     */
    private const EXCLUDED_CHANNEL_IDS = [6];

    public function __construct(Simpla $db)
    {
        $this->db = $db;
    }

    /**
     * Получить результаты с пагинацией
     *
     * @param BRReportFilterDto $filter
     * @return array
     */
    public function getResults(BRReportFilterDto $filter): array
    {
        $this->db->query("
            SELECT
                t.id,
                t.created_at,
                JSON_UNQUOTE(JSON_EXTRACT(t.data, '$.source')) as source,
                user.id AS client_id,
                TRIM(CONCAT(user.lastname, ' ', user.firstname, ' ', user.patronymic)) as client_name,
                org.short_name as company_name,
                'микрофинансовая деятельность' as type_activity,
                'предоставление микрозаймов' as type_product,
                ts.name as subject_name,
                tc.name as channel_name,
                tst.name as take_decision,
                tst.name as basis_decision,
                'нет' as scope_consideration
            FROM s_mytickets t
            LEFT JOIN s_mytickets_subjects ts ON t.subject_id = ts.id
            LEFT JOIN s_mytickets_channels tc ON t.chanel_id = tc.id
            LEFT JOIN s_mytickets_statuses tst ON t.status_id = tst.id
            LEFT JOIN s_organizations org ON t.company_id = org.id
            LEFT JOIN s_users user ON t.client_id = user.id
            WHERE DATE(t.created_at) BETWEEN ? AND ?
                AND tst.name NOT IN ('Запрос КО', 'Дубликаты')
                AND (ts.parent_id IS NULL OR ts.parent_id != " . self::TECH_SUPPORT_PARENT_ID . ")
                AND (t.chanel_id IS NULL OR t.chanel_id NOT IN (" . implode(',', self::EXCLUDED_CHANNEL_IDS) . ")) " . $filter->getFiltersWhere() . "
            ORDER BY " . $filter->getOrderBy() . "
            LIMIT ? OFFSET ?",
            $filter->getDateFrom(),
            $filter->getDateTo(),
            $filter->getLimit(),
            $filter->getOffset()
        );

        return $this->db->results() ?: [];
    }

    /**
     * Получить все результаты без пагинации
     *
     * @param BRReportFilterDto $filter
     * @return array
     */
    public function getAllResults(BRReportFilterDto $filter): array
    {
        $this->db->query("
            SELECT
                t.id,
                t.created_at,
                JSON_UNQUOTE(JSON_EXTRACT(t.data, '$.source')) as source,
                user.id AS client_id,
                TRIM(CONCAT(user.lastname, ' ', user.firstname, ' ', user.patronymic)) as client_name,
                org.short_name as company_name,
                'микрофинансовая деятельность' as type_activity,
                'предоставление микрозаймов' as type_product,
                ts.name as subject_name,
                tc.name as channel_name,
                tst.name as take_decision,
                tst.name as basis_decision,
                'нет' as scope_consideration
            FROM s_mytickets t
            LEFT JOIN s_mytickets_subjects ts ON t.subject_id = ts.id
            LEFT JOIN s_mytickets_channels tc ON t.chanel_id = tc.id
            LEFT JOIN s_mytickets_statuses tst ON t.status_id = tst.id
            LEFT JOIN s_organizations org ON t.company_id = org.id
            LEFT JOIN s_users user ON t.client_id = user.id
            WHERE DATE(t.created_at) BETWEEN ? AND ?
                AND tst.name NOT IN ('Запрос КО', 'Дубликаты')
                AND (ts.parent_id IS NULL OR ts.parent_id != " . self::TECH_SUPPORT_PARENT_ID . ")
                AND (t.chanel_id IS NULL OR t.chanel_id NOT IN (" . implode(',', self::EXCLUDED_CHANNEL_IDS) . ")) " . $filter->getFiltersWhere() . "
            ORDER BY " . $filter->getOrderBy(),
            $filter->getDateFrom(),
            $filter->getDateTo()
        );

        return $this->db->results() ?: [];
    }

    /**
     * Получить результаты чанками для экспорта (генератор)
     *
     * @param BRReportFilterDto $filter
     * @param int $chunkSize
     * @return Generator
     */
    public function getChunkedResults(BRReportFilterDto $filter, int $chunkSize = 100): Generator
    {
        $offset = 0;

        while (true) {
            $this->db->query("
                SELECT
                    t.id,
                    t.created_at,
                    JSON_UNQUOTE(JSON_EXTRACT(t.data, '$.source')) as source,
                    user.id AS client_id,
                    TRIM(CONCAT(user.lastname, ' ', user.firstname, ' ', user.patronymic)) as client_name,
                    org.short_name as company_name,
                    'микрофинансовая деятельность' as type_activity,
                    'предоставление микрозаймов' as type_product,
                    ts.name as subject_name,
                    tc.name as channel_name,
                    tst.name as take_decision,
                    tst.name as basis_decision,
                    'нет' as scope_consideration
                FROM s_mytickets t
                LEFT JOIN s_mytickets_subjects ts ON t.subject_id = ts.id
                LEFT JOIN s_mytickets_channels tc ON t.chanel_id = tc.id
                LEFT JOIN s_mytickets_statuses tst ON t.status_id = tst.id
                LEFT JOIN s_organizations org ON t.company_id = org.id
                LEFT JOIN s_users user ON t.client_id = user.id
                WHERE DATE(t.created_at) BETWEEN ? AND ?
                    AND tst.name NOT IN ('Запрос КО', 'Дубликаты')
                    AND (ts.parent_id IS NULL OR ts.parent_id != " . self::TECH_SUPPORT_PARENT_ID . ")
                    AND (t.chanel_id IS NULL OR t.chanel_id NOT IN (" . implode(',', self::EXCLUDED_CHANNEL_IDS) . ")) " . $filter->getFiltersWhere() . "
                ORDER BY " . $filter->getOrderBy() . "
                LIMIT ? OFFSET ?",
                $filter->getDateFrom(),
                $filter->getDateTo(),
                $chunkSize,
                $offset
            );

            $items = $this->db->results();
            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                yield $item;
            }

            $offset += $chunkSize;
        }
    }

    /**
     * Получить общее количество записей
     *
     * @param BRReportFilterDto $filter
     * @return int
     */
    public function getTotals(BRReportFilterDto $filter): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(t.id) AS total
            FROM s_mytickets t
            LEFT JOIN s_mytickets_subjects ts ON t.subject_id = ts.id
            LEFT JOIN s_mytickets_channels tc ON t.chanel_id = tc.id
            LEFT JOIN s_mytickets_statuses tst ON t.status_id = tst.id
            LEFT JOIN s_organizations org ON t.company_id = org.id
            WHERE DATE(t.created_at) BETWEEN ? AND ?
                AND tst.name NOT IN ('Запрос КО', 'Дубликаты')
                AND (ts.parent_id IS NULL OR ts.parent_id != " . self::TECH_SUPPORT_PARENT_ID . ")
                AND (t.chanel_id IS NULL OR t.chanel_id NOT IN (" . implode(',', self::EXCLUDED_CHANNEL_IDS) . ")) " . $filter->getFiltersWhere(),
            $filter->getDateFrom(),
            $filter->getDateTo()
        );

        $this->db->query($query);
        return (int)$this->db->result('total');
    }
}
