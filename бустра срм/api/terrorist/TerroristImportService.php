<?php

declare(strict_types=1);

namespace api\terrorist;

use api\terrorist\models\TerroristSources;
use Database;
use RuntimeException;

class TerroristImportService
{
    /** @var Database */
    private $db;

    private TerroristImporterFactory $factory;
    private TerroristSources $terroristSources;

    public function __construct($db)
    {
        $this->db = $db;
        $this->factory = new TerroristImporterFactory();
        $this->terroristSources = new TerroristSources($this->db);
    }

    /**
     * Импорт по коду источника (mvk_decision / un_consolidated / default) и пути файла.
     */
    public function importFile(string $sourceCode, object $file): int
    {
        $sourceId = $this->terroristSources->getIdByCode($sourceCode);

        if ($sourceId === null) {
            throw new RuntimeException("Unknown source code: {$sourceCode}");
        }

        $importer = $this->factory->create(
            $sourceCode,
            $this->db,
            $sourceId,
            $file
        );

        return $importer->import($file->file_path);
    }
}
