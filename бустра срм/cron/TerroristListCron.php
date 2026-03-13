<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '12000');

chdir(dirname(__FILE__) . '/../');
require_once 'api/Simpla.php';
require_once 'vendor/autoload.php';

use api\terrorist\models\TerroristImportFiles;
use api\terrorist\models\TerroristSources;
use api\terrorist\TerroristImportService;
/**
 * Cron для обработки загруженных XML-списков террористов.
 *
 * ВАЖНО:
 *  - за один запуск обрабатывается ТОЛЬКО ОДИН файл
 *  - защита от гонок по статусу (uploaded/scheduled -> in_progress)
 */
class TerroristListCron extends Simpla
{
    private const LOG_FILE = 'terrorist_import.txt';

    /** @var TerroristImportService */
    private TerroristImportService $importService;
    private TerroristImportFiles   $importFilesModel;
    private TerroristSources       $sourcesModel;

    public function __construct()
    {
        parent::__construct();
        $this->importService = new TerroristImportService($this->db);
        $this->importFilesModel   = new TerroristImportFiles($this->db);
        $this->sourcesModel       = new TerroristSources($this->db);
        $this->run();
    }

    /**
     * Основной запуск крона
     */
    public function run(): void
    {
        // Берём следующий файл из очереди
        $file = $this->importFilesModel->getNextQueuedFile();

        if (!$file) {
            // нет файлов в очереди – просто выходим
            return;
        }

        $this->processFile($file);
    }

    /**
     * Обработка одного файла (с защитой от гонок по статусу)
     *
     * @param object $file
     */
    private function processFile(object $file): void
    {
        // Пытаемся «захватить» файл этим процессом
        if (!$this->importFilesModel->lockFileForProcessing((int)$file->id)) {
            // кто-то другой уже взял этот файл
            return;
        }

        try {
            // 1) Путь к файлу
            $path = $file->file_path ?? null;
            if (!$path || !is_file($path)) {
                throw new RuntimeException("File not found: {$path}");
            }

            // 2) Находим источник и его код (s_terrorist_sources.code)
            if (empty($file->source_id)) {
                throw new RuntimeException("Missing source_id for file id={$file->id}");
            }

            $source = $this->sourcesModel->getSourceById((int)$file->source_id);
            if (!$source) {
                throw new RuntimeException("Source not found for id={$file->source_id}");
            }

            $sourceCode = $source->code;

            // 3) Импортируем файл через сервис
            $rowsImported = $this->importService->importFile($sourceCode, $file);

            // 4) Успешное завершение
            $this->importFilesModel->markFileDone((int)$file->id, $rowsImported);

        } catch (Throwable $e) {
            // 5) Фиксируем ошибку в таблице файлов
            $this->importFilesModel->markFileError((int)$file->id, $e->getMessage());

            // 6) Логируем подробности
            $this->logging(
                __METHOD__,
                '',
                $e->getMessage(),
                ['file' => $file],
                self::LOG_FILE
            );
        }
    }
}

new TerroristListCron();
