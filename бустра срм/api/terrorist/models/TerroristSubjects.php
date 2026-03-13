<?php

namespace api\terrorist\models;

/**
 * Работа с таблицей s_terrorist_subjects (актуальное состояние субъектов)
 */
class TerroristSubjects
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Батчевый upsert в s_terrorist_subjects (актуальное состояние).
     *
     * Ожидаемые ключи в $rows:
     *  - source_id
     *  - external_id
     *  - full_name, last_name, first_name, middle_name, latin_full_name
     *  - date_of_birth, year_of_birth, place_of_birth
     *  - gender, nationality
     *  - inn, snils
     *  - person_type_name
     *  - is_terrorist
     *  - list_date (для first_seen/last_seen)
     *  - created_at, updated_at (опционально)
     *
     * @param array<int,array<string,mixed>> $rows
     */
    public function upsertBatch(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $columns = [
            'source_id',
            'external_id',
            'full_name',
            'last_name',
            'first_name',
            'middle_name',
            'latin_full_name',
            'date_of_birth',
            'year_of_birth',
            'place_of_birth',
            'gender',
            'nationality',
            'inn',
            'snils',
            'person_type_name',
            'is_terrorist',
            'is_current',
            'first_seen_date',
            'last_seen_date',
            'created_at',
            'updated_at',
        ];

        // Хелпер: приводим значение к SQL-литералу
        $toSql = function ($value) {
            if ($value === null) {
                return 'NULL';
            }

            // Булевы и числа без кавычек
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            if (is_int($value) || is_float($value)) {
                return (string)$value;
            }

            // DateTime → строка
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            // Всё остальное — строка в кавычках
            return "'" . $this->db->escape((string)$value) . "'";
        };

        $valuesSqlParts = [];

        foreach ($rows as $row) {
            $listDate  = $row['list_date'] ?? null;
            $createdAt = $row['created_at'] ?? date('Y-m-d H:i:s');
            $updatedAt = $row['updated_at'] ?? date('Y-m-d H:i:s');

            $rowValues = [];

            // порядок ДОЛЖЕН соответствовать $columns
            $rowValues[] = $toSql($row['source_id']        ?? null);
            $rowValues[] = $toSql($row['external_id']      ?? null);
            $rowValues[] = $toSql($row['full_name']        ?? null);
            $rowValues[] = $toSql($row['last_name']        ?? null);
            $rowValues[] = $toSql($row['first_name']       ?? null);
            $rowValues[] = $toSql($row['middle_name']      ?? null);
            $rowValues[] = $toSql($row['latin_full_name']  ?? null);
            $rowValues[] = $toSql($row['date_of_birth']    ?? null);
            $rowValues[] = $toSql($row['year_of_birth']    ?? null);
            $rowValues[] = $toSql($row['place_of_birth']   ?? null);
            $rowValues[] = $toSql($row['gender']           ?? null);
            $rowValues[] = $toSql($row['nationality']      ?? null);
            $rowValues[] = $toSql($row['inn']              ?? null);
            $rowValues[] = $toSql($row['snils']            ?? null);
            $rowValues[] = $toSql($row['person_type_name'] ?? null);
            $rowValues[] = $toSql(isset($row['is_terrorist']) ? (int)$row['is_terrorist'] : 1);

            // is_current = 1 в рамках текущего перечня
            $rowValues[] = $toSql(1);

            // first/last_seen_date = дата текущего перечня
            $rowValues[] = $toSql($listDate);
            $rowValues[] = $toSql($listDate);

            $rowValues[] = $toSql($createdAt);
            $rowValues[] = $toSql($updatedAt);

            // Одна строка VALUES (...)
            $valuesSqlParts[] = '(' . implode(',', $rowValues) . ')';
        }

        $sql = "
        INSERT INTO s_terrorist_subjects
        (" . implode(',', $columns) . ")
        VALUES " . implode(',', $valuesSqlParts) . "
        ON DUPLICATE KEY UPDATE
            full_name        = VALUES(full_name),
            last_name        = VALUES(last_name),
            first_name       = VALUES(first_name),
            middle_name      = VALUES(middle_name),
            latin_full_name  = VALUES(latin_full_name),
            date_of_birth    = VALUES(date_of_birth),
            year_of_birth    = VALUES(year_of_birth),
            place_of_birth   = VALUES(place_of_birth),
            gender           = VALUES(gender),
            nationality      = VALUES(nationality),
            inn              = VALUES(inn),
            snils            = VALUES(snils),
            person_type_name = VALUES(person_type_name),
            is_terrorist     = VALUES(is_terrorist),
            is_current       = VALUES(is_current),
            first_seen_date  = IF(
                first_seen_date IS NULL,
                VALUES(first_seen_date),
                LEAST(first_seen_date, VALUES(first_seen_date))
            ),
            last_seen_date   = IF(
                last_seen_date IS NULL,
                VALUES(last_seen_date),
                GREATEST(last_seen_date, VALUES(last_seen_date))
            ),
            updated_at       = VALUES(updated_at)
    ";

        $this->db->query($sql);
    }

    /**
     * Вернуть subject_id по external_id для данного source_id.
     *
     * @param int   $sourceId
     * @param array $externalIds
     * @return array<string,int> [external_id => subject_id]
     */
    public function getIdsByExternalIds(int $sourceId, array $externalIds): array
    {
        $externalIds = array_values(array_unique(array_filter($externalIds, 'strlen')));
        if (empty($externalIds)) {
            return [];
        }

        $sql = $this->db->placehold("
            SELECT id, external_id
            FROM s_terrorist_subjects
            WHERE source_id = ?
              AND external_id IN (?@)
        ", $sourceId, $externalIds);

        $this->db->query($sql);
        $rows = $this->db->results();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->external_id] = (int)$row->id;
        }

        return $map;
    }


    /**
     * Сбрасываем is_current у тех, кто "выпал" из актуального перечня.
     *
     * @param int    $sourceId
     * @param string $listDate Y-m-d
     * @param string|null $now
     */
    public function finalizeCurrentFlags(int $sourceId, string $listDate, ?string $now = null): void
    {
        $now = $now ?? date('Y-m-d H:i:s');

        $sql = "
            UPDATE s_terrorist_subjects
            SET 
                is_current = 0,
                updated_at = ?
            WHERE source_id = ?
              AND (
                  last_seen_date IS NULL
                  OR last_seen_date < ?
              )
        ";

        $this->db->query(
            $this->db->placehold($sql, $now, $sourceId, $listDate)
        );
    }

    /**
     * Актуальный список субъектов (is_current = 1) по всем источникам.
     *
     * Фильтры:
     *  - page  (int)
     *  - limit (int)
     *  - query (string) — поиск по ФИО / ИНН / СНИЛС
     */
    public function getCurrents(array $filter = []): array
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
                subj.source_id,
                src.code AS source_code,
                src.name AS source_name
            FROM s_terrorist_subjects AS subj
                INNER JOIN s_terrorist_sources AS src
                    ON src.id = subj.source_id
            WHERE subj.is_current = 1
                $searchFilter
            ORDER BY subj.full_name, subj.id
            $sqlLimit
        ");

        $this->db->query($querySql);

        /** @var array<int,object> */
        return (array) $this->db->results();
    }

    /**
     * Общее число актуальных субъектов (для пагинации актуального реестра)
     */
    public function countCurrent(array $filter = []): int
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
            FROM s_terrorist_subjects AS subj
                INNER JOIN s_terrorist_sources AS src
                    ON src.id = subj.source_id
            WHERE subj.is_current = 1
                $searchFilter
        ");

        $this->db->query($querySql);
        $row = $this->db->result();

        return (int) ($row->cnt ?? 0);
    }


    /**
     * Найти совпадения по клиенту в актуальных террористических списках.
     *
     * @param array $client ['inn'?, 'snils'?, 'full_name'?, 'date_of_birth'?]
     * @param int $limit
     * @return array<int,object>
     */
    public function findMatchesForClient(array $client, int $limit = 50): array
    {
        $inn      = (string)($client['inn'] ?? '');
        $snils    = (string)($client['snils'] ?? '');
        $fullName = (string)($client['full_name'] ?? '');
        $dob      = (string)($client['date_of_birth'] ?? '');

        // Если нечем матчить — сразу пусто
        if ($inn === '' && $snils === '' && ($fullName === '' || $dob === '')) {
            return [];
        }

        $where  = [];
        $params = [];

        if ($inn !== '') {
            $where[]  = 'subj.inn = ?';
            $params[] = $inn;
        }

        if ($snils !== '') {
            $where[]  = 'subj.snils = ?';
            $params[] = $snils;
        }

        if ($fullName !== '' && $dob !== '') {
            $where[]  = '(UPPER(subj.full_name) = ? AND subj.date_of_birth = ?)';
            $params[] = $fullName;
            $params[] = $dob;
        }

        $sql = "
        SELECT
            subj.*,
            src.code AS source_code,
            src.name AS source_name,

            (
                SELECT l.list_date
                FROM s_terrorist_subject_lists l
                WHERE l.subject_id = subj.id
                ORDER BY l.list_date DESC, l.import_file_id DESC
                LIMIT 1
            ) AS list_date,

            (
                SELECT l.import_file_id
                FROM s_terrorist_subject_lists l
                WHERE l.subject_id = subj.id
                ORDER BY l.list_date DESC, l.import_file_id DESC
                LIMIT 1
            ) AS import_file_id

        FROM s_terrorist_subjects subj
        LEFT JOIN s_terrorist_sources src ON src.id = subj.source_id

        WHERE subj.is_current = 1
          AND subj.is_terrorist = 1
          AND (" . implode(' OR ', $where) . ")

        ORDER BY subj.last_seen_date DESC
        LIMIT " . $limit . "
    ";

        $query = $this->db->placehold($sql, ...$params);
        $this->db->query($query);

        return (array)$this->db->results();
    }
}
