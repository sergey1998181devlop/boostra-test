<?php

namespace api\terrorist\models;

use Database;

/**
 * Работа с таблицей s_terrorist_subject_lists (история изменений по перечням)
 */
class TerroristSubjectLists
{
    /** @var Database */
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Батч вставки истории в s_terrorist_subject_lists.
     *
     * Ожидаемые ключи в $rows:
     *  - subject_id      (int)
     *  - import_file_id  (int)
     *  - source_id       (int)
     *  - list_date       (Y-m-d)
     *  - is_terrorist    (0|1, опционально, по умолчанию 1)
     *  - created_at      (datetime, опционально, по умолчанию NOW)
     *
     * @param array<int,array<string,mixed>> $rows
     */
    public function insertHistoryBatch(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $columns = [
            'subject_id',
            'import_file_id',
            'source_id',
            'list_date',
            'is_terrorist',
            'created_at',
        ];

        // Хелпер: приводим PHP-значение к SQL-литералу
        $toSql = function ($value) {
            if ($value === null) {
                return 'NULL';
            }

            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            if (is_int($value) || is_float($value)) {
                return (string)$value;
            }

            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            return "'" . $this->db->escape((string)$value) . "'";
        };

        $valuesSqlParts = [];

        foreach ($rows as $row) {
            // Пропускаем явно битые строки
            if (
                empty($row['subject_id']) ||
                empty($row['import_file_id']) ||
                empty($row['source_id']) ||
                empty($row['list_date'])
            ) {
                continue;
            }

            $isTerrorist = array_key_exists('is_terrorist', $row)
                ? (int)$row['is_terrorist']
                : 1;

            $createdAt = $row['created_at'] ?? date('Y-m-d H:i:s');

            $rowValues   = [];
            $rowValues[] = $toSql((int)$row['subject_id']);
            $rowValues[] = $toSql((int)$row['import_file_id']);
            $rowValues[] = $toSql((int)$row['source_id']);
            $rowValues[] = $toSql($row['list_date']);
            $rowValues[] = $toSql($isTerrorist);
            $rowValues[] = $toSql($createdAt);

            $valuesSqlParts[] = '(' . implode(',', $rowValues) . ')';
        }

        if (!$valuesSqlParts) {
            return;
        }

        $sql = "
        INSERT INTO s_terrorist_subject_lists
            (" . implode(',', $columns) . ")
        VALUES " . implode(',', $valuesSqlParts) . "
        ON DUPLICATE KEY UPDATE
            is_terrorist = VALUES(is_terrorist)
    ";

        $this->db->query($sql);
    }


    /**
     * Список субъектов по конкретному файлу.
     *
     * Фильтры:
     *  - page  (int)
     *  - limit (int)
     *  - query (string) — поиск по ФИО / ИНН / СНИЛС
     */
    public function getSubjects(int $importFileId, array $filter = []): array
    {
        $limit = isset($filter['limit']) ? max(1, (int) $filter['limit']) : 50;
        $page  = isset($filter['page'])  ? max(1, (int) $filter['page'])  : 1;
        $query = isset($filter['query']) ? trim((string) $filter['query']) : '';

        $searchFilter = '';
        if ($query !== '') {
            $like = '%' . $query . '%';
            $searchFilter = $this->db->placehold(
                " AND (subj.full_name LIKE ? OR subj.inn LIKE ? OR subj.snils LIKE ?)",
                $like,
                $like,
                $like
            );
        }

        $sqlLimit = $this->db->placehold(
            ' LIMIT ?, ? ',
            ($page - 1) * $limit,
            $limit
        );

        $querySql = $this->db->placehold("
            SELECT
                subj.id,
                subj.full_name,
                subj.date_of_birth,
                subj.inn,
                subj.snils,
                subj.is_terrorist,
                subj.is_current,
                subj.first_seen_date,
                subj.last_seen_date,
                l.list_date
            FROM s_terrorist_subject_lists AS l
                INNER JOIN s_terrorist_subjects AS subj
                    ON subj.id = l.subject_id
            WHERE l.import_file_id = ?
                $searchFilter
            ORDER BY subj.full_name, subj.id
            $sqlLimit
        ", $importFileId);

        $this->db->query($querySql);

        /** @var array<int,object> */
        return (array) $this->db->results();
    }

    /**
     * Общее число субъектов по файлу (для пагинации)
     */
    public function countSubjects(int $importFileId, array $filter = []): int
    {
        $query = isset($filter['query']) ? trim((string) $filter['query']) : '';

        $searchFilter = '';
        if ($query !== '') {
            $like = '%' . $query . '%';
            $searchFilter = $this->db->placehold(
                " AND (subj.full_name LIKE ? OR subj.inn LIKE ? OR subj.snils LIKE ?)",
                $like,
                $like,
                $like
            );
        }

        $querySql = $this->db->placehold("
            SELECT COUNT(DISTINCT subj.id) AS cnt
            FROM s_terrorist_subject_lists AS l
                INNER JOIN s_terrorist_subjects AS subj
                    ON subj.id = l.subject_id
            WHERE l.import_file_id = ?
                $searchFilter
        ", $importFileId);

        $this->db->query($querySql);
        $row = $this->db->result();

        return (int) ($row->cnt ?? 0);
    }
}
