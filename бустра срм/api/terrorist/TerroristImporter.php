<?php

namespace api\terrorist;

use api\terrorist\models\TerroristSubjects;
use api\terrorist\models\TerroristSubjectLists;
use Database;
use RuntimeException;

/**
 * Базовый импортёр списков террористов.
 * Отвечает за общую обвязку, а парсинг конкретного формата — в наследниках.
 */
abstract class TerroristImporter
{
    protected $db;

    protected int $sourceId;
    protected object $file;

    /** Размер батча по умолчанию */
    protected int $batchSize = 50;

    protected TerroristSubjects $subjectsModel;

    protected TerroristSubjectLists $subjectListsModel;

    /**
     * @param Database $db
     * @param int      $sourceId
     * @param object   $file
     */
    public function __construct($db, int $sourceId, object $file)
    {
        $this->db       = $db;
        $this->sourceId = $sourceId;
        $this->file = $file;

        $this->subjectsModel     = new TerroristSubjects($db);
        $this->subjectListsModel = new TerroristSubjectLists($db);
    }

    /**
     * Обёртка: проверка файла и вызов конкретной реализации.
     */
    final public function import(string $filePath): int
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("File not readable: {$filePath}");
        }

        return $this->doImport($filePath);
    }

    /**
     * Конкретная реализация парсинга XML и формирования батчей.
     *
     * @param string $filePath
     * @return int кол-во импортированных записей/строк
     */
    abstract protected function doImport(string $filePath): int;


    /**
     * Обработка одного батча:
     *  1) upsert субъектов в s_terrorist_subjects
     *  2) запись истории в s_terrorist_subject_lists по subject_id
     *
     * @param array<int,array<string,mixed>> $batch
     */
    protected function flushBatch(array $batch): void
    {
        if (empty($batch)) {
            return;
        }

        // 1) Обновляем актуальное состояние
        $this->subjectsModel->upsertBatch($batch);

        // 2) Получаем subject_id по external_id для этого source_id
        $externalIds = [];
        foreach ($batch as $row) {
            if (!empty($row['external_id'])) {
                $externalIds[] = $row['external_id'];
            }
        }

        $map = $this->subjectsModel->getIdsByExternalIds($this->sourceId, $externalIds);
        if (empty($map)) {
            return;
        }

        // 3) Собираем историю для s_terrorist_subject_lists
        $historyRows = [];
        foreach ($batch as $row) {
            $ext = $row['external_id'] ?? null;
            if (!$ext || empty($map[$ext])) {
                continue;
            }

            $historyRows[] = [
                'subject_id'     => $map[$ext],
                'import_file_id' => $this->file->id,
                'source_id'      => $this->sourceId,
                'list_date'      => $row['list_date'],
                'is_terrorist'   => $row['is_terrorist'] ?? 1,
                'created_at'     => $row['created_at'] ?? date('Y-m-d H:i:s'),
            ];
        }

        if ($historyRows) {
            $this->subjectListsModel->insertHistoryBatch($historyRows);
        }
    }
    /**
     * Нормализация даты (разные форматы → Y-m-d).
     */
    protected function normalizeDate(?string $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        // Уже нормальный формат с временем
        if (strlen($value) > 10 && preg_match('~^\d{4}-\d{2}-\d{2}~', $value, $m)) {
            return $m[0];
        }

        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
