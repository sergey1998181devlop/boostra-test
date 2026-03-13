<?php

declare(strict_types=1);

namespace api\terrorist\models;

/**
 * Работа с таблицей s_terrorist_import_files
 */
class TerroristImportFiles
{
    public const STATUS_UPLOADED   = 'uploaded';
    public const STATUS_QUEUED     = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE       = 'done';
    public const STATUS_ERROR      = 'error';

    /** @var \Database|\stdClass */
    private $db;

    /**
     * @param \Database|\stdClass $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Добавить запись о загруженном файле
     *
     * @param array $fileData
     *  [
     *      'source_id'         => int,
     *      'original_filename' => string,
     *      'stored_filename'   => string,
     *      'file_path'         => string,
     *      'file_size'         => int|null,
     *      'file_hash'         => string|null,
     *      'status'            => string,     // uploaded|queued|processing|done|error
     *      'error_message'     => string|null,
     *      'total_records'     => int,
     *      'processed_records' => int,
     *      'uploaded_by'       => int|null,
     *      'uploaded_at'       => datetime,
     *      'processed_at'      => datetime|null,
     *      'created_at'        => datetime,
     *  ]
     *
     * @return int id вставленной записи
     */
    public function addImportFile(array $fileData): int
    {
        $now = date('Y-m-d H:i:s');

        $defaults = [
            'source_id'         => null,
            'original_filename' => '',
            'stored_filename'   => '',
            'file_path'         => '',
            'file_size'         => null,
            'file_hash'         => null,
            'status'            => self::STATUS_UPLOADED,
            'error_message'     => null,
            'total_records'     => 0,
            'processed_records' => 0,
            'uploaded_by'       => null,
            'uploaded_at'       => $now,
            'processed_at'      => null,
            'created_at'        => $now,
        ];

        $fileData = array_merge($defaults, $fileData);

        $query = $this->db->placehold("
            INSERT INTO s_terrorist_import_files SET ?%
        ", $fileData);

        $this->db->query($query);

        return (int) $this->db->insert_id();
    }

    /**
     * Получить один файл по id
     *
     * @param int $id
     * @return object|null
     */
    public function getImportFile(int $id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM s_terrorist_import_files
            WHERE id = ?
            LIMIT 1
        ", $id);

        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Список файлов с агрегированными данными по субъектам.
     *
     * Фильтры:
     *  - source_code (string|array)  код(ы) источника
     *  - source_id   (int|array)     id источника
     *  - status      (string|array)  статус(ы) файла
     *  - page        (int)
     *  - limit       (int)
     *
     * Возвращает массив объектов с полями:
     *  - f.* (все поля из s_terrorist_import_files)
     *  - source_code
     *  - source_name
     *  - subjects_total
     *  - subjects_current
     *
     * @param array $filter
     * @return array<int,object>
     */
    public function getFiles(array $filter = []): array
    {
        $sourceCodeFilter = '';
        $sourceIdFilter   = '';
        $statusFilter     = '';

        $limit = isset($filter['limit']) ? max(1, (int) $filter['limit']) : 100;
        $page  = isset($filter['page'])  ? max(1, (int) $filter['page'])  : 1;

        if (!empty($filter['source_code'])) {
            $codes = (array) $filter['source_code'];
            $sourceCodeFilter = $this->db->placehold("AND src.code IN (?@)", $codes);
        }

        if (!empty($filter['source_id'])) {
            $ids = array_map('intval', (array) $filter['source_id']);
            $sourceIdFilter = $this->db->placehold("AND f.source_id IN (?@)", $ids);
        }

        if (!empty($filter['status'])) {
            $statuses = (array) $filter['status'];
            $statusFilter = $this->db->placehold("AND f.status IN (?@)", $statuses);
        }

        $offset  = ($page - 1) * $limit;
        $sqlLimit = $this->db->placehold(' LIMIT ?, ? ', $offset, $limit);

        $query = $this->db->placehold("
            SELECT
                f.*,
                src.code AS source_code,
                src.name AS source_name,
                COUNT(DISTINCT l.subject_id) AS subjects_total,
                COUNT(DISTINCT CASE WHEN subj.is_current = 1 THEN subj.id END) AS subjects_current
            FROM s_terrorist_import_files AS f
                INNER JOIN s_terrorist_sources AS src
                    ON src.id = f.source_id
                LEFT JOIN s_terrorist_subject_lists AS l
                    ON l.import_file_id = f.id
                LEFT JOIN s_terrorist_subjects AS subj
                    ON subj.id = l.subject_id
            WHERE 1
                $sourceCodeFilter
                $sourceIdFilter
                $statusFilter
            GROUP BY f.id
            ORDER BY f.id DESC
            $sqlLimit
        ");

        $this->db->query($query);

        return (array) $this->db->results();
    }

    /**
     * Обновить запись файла
     *
     * @param int   $id
     * @param array $fields
     */
    public function updateImportFile(int $id, array $fields): void
    {
        if (empty($fields)) {
            return;
        }

        $query = $this->db->placehold("
            UPDATE s_terrorist_import_files SET ?% WHERE id = ?
        ", $fields, $id);

        $this->db->query($query);
    }

    /**
     * Взять следующий файл в очереди (uploaded / queued)
     *
     * @return object|null
     */
    public function getNextQueuedFile()
    {
        $statuses = [
            self::STATUS_UPLOADED,
            self::STATUS_QUEUED,
        ];

        $query = $this->db->placehold("
            SELECT *
            FROM s_terrorist_import_files
            WHERE status IN (?@)
            ORDER BY id ASC
            LIMIT 1
        ", $statuses);

        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Попробовать залочить файл для обработки этим процессом.
     * Меняет статус на STATUS_PROCESSING только если статус был STATUS_UPLOADED/STATUS_QUEUED.
     *
     * @param int $id
     * @return bool true, если файл успешно залочен
     */
    public function lockFileForProcessing(int $id): bool
    {
        $allowedStatuses = [
            self::STATUS_UPLOADED,
            self::STATUS_QUEUED,
        ];

        $query = $this->db->placehold("
            UPDATE s_terrorist_import_files
            SET status = ?
            WHERE id = ?
              AND status IN (?@)
        ", self::STATUS_PROCESSING, $id, $allowedStatuses);

        $this->db->query($query);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Пометить файл как успешно обработанный
     *
     * @param int $id
     * @param int $rowsImported
     */
    public function markFileDone(int $id, int $rowsImported): void
    {
        // Если total_records ещё не установлен, проставляем его в rowsImported
        $query = $this->db->placehold("
            UPDATE s_terrorist_import_files
            SET status            = ?,
                processed_records = ?,
                total_records     = IF(total_records = 0, ?, total_records),
                error_message     = NULL,
                processed_at      = NOW()
            WHERE id = ?
        ", self::STATUS_DONE, $rowsImported, $rowsImported, $id);

        $this->db->query($query);
    }

    /**
     * Пометить файл как завершившийся с ошибкой
     *
     * @param int    $id
     * @param string $message
     */
    public function markFileError(int $id, string $message): void
    {
        $message = mb_substr($message, 0, 1000);

        $query = $this->db->placehold("
            UPDATE s_terrorist_import_files
            SET status        = ?,
                error_message = ?
            WHERE id = ?
        ", self::STATUS_ERROR, $message, $id);

        $this->db->query($query);
    }
}
