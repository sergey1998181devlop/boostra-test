<?php

namespace App\Repositories;

use Database;

class AIBotCallsRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Построение WHERE-условий и параметров на основе фильтров
     * 
     * @param array $filters
     * @return array ['sql' => string, 'params' => array]
     */
    private function buildFiltersWhereClause(array $filters): array
    {
        $whereParts = [];
        $params = [];

        if (!empty($filters['phone_mobile'])) {
            $whereParts[] = "(u.phone_mobile LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.msisdn')) LIKE ?)";
            $params[] = '%' . $filters['phone_mobile'] . '%';
            $params[] = '%' . $filters['phone_mobile'] . '%';
        }

        if (!empty($filters['client_fio'])) {
            $whereParts[] = "CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) LIKE ?";
            $params[] = '%' . $filters['client_fio'] . '%';
        }

        if (!empty($filters['tag'])) {
            $whereParts[] = "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.tag')) = ?";
            $params[] = $filters['tag'];
        }

        if (!empty($filters['bot_action'])) {
            $searchPattern = str_replace(['\\%', '\\_'], ['%', '_'], $filters['bot_action']);

            $conditions = [];
            for ($i = 0; $i < 20; $i++) {
                $jsonPath = '$.methods_list[' . $i . ']';
                $conditions[] = "(JSON_VALID(c.text) = 1 
                    AND JSON_EXTRACT(c.text, '{$jsonPath}') IS NOT NULL
                    AND JSON_UNQUOTE(JSON_EXTRACT(c.text, '{$jsonPath}')) LIKE ?)";
                $params[] = '%' . $searchPattern . '%';
            }
            
            $whereParts[] = "(JSON_VALID(c.text) = 1
                AND JSON_TYPE(JSON_EXTRACT(c.text, '$.methods_list')) = 'ARRAY'
                AND JSON_LENGTH(JSON_EXTRACT(c.text, '$.methods_list')) > 0
                AND (" . implode(' OR ', $conditions) . "))";
        }

        if (isset($filters['transferred_to_operator'])) {
            if ($filters['transferred_to_operator'] === 'Да') {
                $whereParts[] = "(
                    JSON_EXTRACT(c.text, '$.switch_to_operator') = true
                    OR JSON_EXTRACT(c.text, '$.switch_to_operator') = 1
                    OR JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.switch_to_operator')) IN ('true','1')
                )";
            } elseif ($filters['transferred_to_operator'] === 'Нет') {
                $whereParts[] = "(
                    JSON_EXTRACT(c.text, '$.switch_to_operator') = false
                    OR JSON_EXTRACT(c.text, '$.switch_to_operator') = 0
                    OR JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.switch_to_operator')) IN ('false','0')
                    OR JSON_EXTRACT(c.text, '$.switch_to_operator') IS NULL
                )";
            }
        }

        return [
            'sql' => !empty($whereParts) ? ' AND (' . implode(') AND (', $whereParts) . ')' : '',
            'params' => $params
        ];
    }

    /**
     * Построение ORDER BY на основе сортировки
     * 
     * @param array $sorting
     * @return string
     */
    private function buildOrderByClause(array $sorting): string
    {
        $field = $sorting['field'] ?? 'date_time';
        $direction = strtoupper($sorting['direction'] ?? 'DESC');
        $direction = ($direction === 'ASC') ? 'ASC' : 'DESC';

        switch ($field) {
            case 'date_time':
                return "c.created {$direction}";
            case 'phone_mobile':
                return "u.phone_mobile {$direction}";
            case 'duration':
                return "JSON_EXTRACT(c.text, '$.duration') {$direction}";
            case 'client_fio':
                return "CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) {$direction}";
            case 'tag':
                return "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.tag')) {$direction}";
            case 'assessment':
                return "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.assessment')) {$direction}";
            case 'transferred_to_operator':
                return "JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.switch_to_operator')) {$direction}";
            default:
                return "c.created DESC";
        }
    }

    /**
     * Получить звонки с пагинацией на основе фильтров
     * 
     * @param array $filters Фильтры поиска
     * @param string $dateFrom Дата начала периода
     * @param string $dateTo Дата окончания периода
     * @param array $pagination ['page_capacity' => int, 'current_page' => int]
     * @param array $sorting ['field' => string, 'direction' => string]
     * @return array
     */
    public function findByFilters(array $filters, string $dateFrom, string $dateTo, array $pagination, array $sorting): array
    {
        $dateFromFull = $dateFrom . ' 00:00:00';
        $dateToFull = date('Y-m-d H:i:s', strtotime($dateTo . ' 00:00:00 +1 day'));
        
        $whereClause = $this->buildFiltersWhereClause($filters);
        $orderBy = $this->buildOrderByClause($sorting);
        
        $limit = $pagination['page_capacity'];
        $offset = $pagination['page_capacity'] * ($pagination['current_page'] - 1);
        
        $queryParams = array_merge(
            [$dateFromFull, $dateToFull],
            $whereClause['params'],
            [$limit, $offset]
        );
        
        $this->db->query("
            SELECT 
                c.id,
                c.created,
                c.text AS call_data,
                c.user_id,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.phone_mobile
            FROM s_comments c
            LEFT JOIN s_users u ON u.id = c.user_id
            WHERE c.block = 'fromtechIncomingCall'
                AND JSON_VALID(c.text) = 1
                AND c.created >= ? AND c.created < ? " . $whereClause['sql'] . "
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?",
            ...$queryParams
        );

        return $this->db->results();
    }

    /**
     * Подсчитать количество звонков на основе фильтров
     * 
     * @param array $filters Фильтры поиска
     * @param string $dateFrom Дата начала периода
     * @param string $dateTo Дата окончания периода
     * @return int
     */
    public function countByFilters(array $filters, string $dateFrom, string $dateTo): int
    {
        $dateFromFull = $dateFrom . ' 00:00:00';
        $dateToFull = date('Y-m-d H:i:s', strtotime($dateTo . ' 00:00:00 +1 day'));
        
        $whereClause = $this->buildFiltersWhereClause($filters);
        
        $queryParams = array_merge(
            [$dateFromFull, $dateToFull],
            $whereClause['params']
        );
        
        $this->db->query("
            SELECT COUNT(*) as count
            FROM s_comments c
            LEFT JOIN s_users u ON u.id = c.user_id
            WHERE c.block = 'fromtechIncomingCall'
                AND JSON_VALID(c.text) = 1
                AND c.created >= ? AND c.created < ? " . $whereClause['sql'],
            ...$queryParams
        );

        $result = $this->db->result();
        return $result->count ?? 0;
    }

    /**
     * Получить звонки для экспорта (генератор для больших объёмов)
     * 
     * @param array $filters Фильтры поиска
     * @param string $dateFrom Дата начала периода
     * @param string $dateTo Дата окончания периода
     * @param array $sorting ['field' => string, 'direction' => string]
     * @param int $chunkSize Размер чанка
     * @return \Generator
     */
    public function findByFiltersChunked(array $filters, string $dateFrom, string $dateTo, array $sorting, int $chunkSize = 100): \Generator
    {
        $dateFromFull = $dateFrom . ' 00:00:00';
        $dateToFull = date('Y-m-d H:i:s', strtotime($dateTo . ' 00:00:00 +1 day'));
        
        $whereClause = $this->buildFiltersWhereClause($filters);
        $orderBy = $this->buildOrderByClause($sorting);

        $lastCreated = null;
        $lastId = null;
        while (true) {
            $keysetSql = '';
            $queryParams = [$dateFromFull, $dateToFull];

            if ($lastCreated !== null && $lastId !== null) {
                $keysetSql = " AND (c.created < ? OR (c.created = ? AND c.id < ?))";
                $queryParams[] = $lastCreated;
                $queryParams[] = $lastCreated;
                $queryParams[] = $lastId;
            }

            $queryParams = array_merge(
                $queryParams,
                $whereClause['params'],
                [$chunkSize]
            );
            
            $this->db->query("
                SELECT 
                    c.id,
                    c.created,
                    c.text AS call_data,
                    c.user_id,
                    u.lastname,
                    u.firstname,
                    u.patronymic,
                    u.phone_mobile
                FROM s_comments c
                LEFT JOIN s_users u ON u.id = c.user_id
                WHERE c.block = 'fromtechIncomingCall'
                    AND JSON_VALID(c.text) = 1
                    AND c.created >= ? AND c.created < ? " . $whereClause['sql'] . $keysetSql . "
                ORDER BY " . $orderBy . "
                LIMIT ?",
                ...$queryParams
            );

            $results = $this->db->results();
            if (empty($results)) {
                break;
            }

            foreach ($results as $result) {
                yield $result;
            }

            $last = end($results);
            $lastCreated = $last->created ?? null;
            $lastId = $last->id ?? null;

            if (count($results) < $chunkSize) {
                break;
            }
        }
    }

    public function getTagsFilterOptions(string $dateFrom, string $dateTo): array
    {
        $dateFromFull = $dateFrom . ' 00:00:00';
        $dateToFull = date('Y-m-d H:i:s', strtotime($dateTo . ' 00:00:00 +1 day'));
        
        $this->db->query("
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.tag')) AS tag
            FROM s_comments c
            WHERE c.block = 'fromtechIncomingCall'
                AND JSON_VALID(c.text) = 1
                AND c.created >= ? AND c.created < ?
                AND JSON_UNQUOTE(JSON_EXTRACT(c.text, '$.tag')) IS NOT NULL
            GROUP BY tag
            ORDER BY tag",
            $dateFromFull, $dateToFull
        );

        $results = $this->db->results();
        $options = ['' => 'Все'];
        if (is_array($results)) {
            foreach ($results as $result) {
                if (!empty($result->tag)) {
                    $options[$result->tag] = $result->tag;
                }
            }
        }

        return $options;
    }
}
