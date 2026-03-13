<?php

declare(strict_types=1);

use api\terrorist\models\TerroristImportFiles;
use api\terrorist\models\TerroristSources;
use api\terrorist\models\TerroristSubjectLists;
use api\terrorist\models\TerroristSubjects;

require_once 'View.php';
require dirname(__DIR__) . '/vendor/autoload.php';

class TerroristListView extends View
{
    private TerroristSources $terroristSources;
    private TerroristImportFiles $terroristImportFiles;
    private TerroristSubjects $terroristSubjects;
    private TerroristSubjectLists $terroristSubjectLists;

    public function __construct()
    {
        parent::__construct();

        $this->terroristSources     = new TerroristSources($this->db);
        $this->terroristImportFiles = new TerroristImportFiles($this->db);
        $this->terroristSubjects = new TerroristSubjects($this->db);
        $this->terroristSubjectLists = new TerroristSubjectLists($this->db);

        $action = $this->request->get('action');
        if ($action && method_exists($this, $action)) {
            $this->{$action}();
            exit;
        }
    }

    /**
     * Основной рендер страницы
     */
    public function fetch(): string
    {
        $sources = $this->terroristSources->getSources();
        $defaultSourceCode = '';

        if (!empty($sources)) {
            $first = reset($sources);
            $defaultSourceCode = (string) $first->code;
        }

        $this->design->assign('sources', $sources);
        $this->design->assign('default_source_code', $defaultSourceCode);
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('terrorist_list.tpl');
    }

    /**
     * Загрузка файла
     */
    public function uploadFile(): void
    {
        $sourceCode = trim((string) $this->request->post('source_code'));

        if (empty($sourceCode)) {
            $this->json_output([
                'success' => false,
                'message' => 'Не указан источник списка',
            ]);
        }

        $source = $this->terroristSources->getSourceByCode($sourceCode);
        if (!$source) {
            $this->json_output([
                'success' => false,
                'message' => 'Неизвестный источник списка',
            ]);
        }

        if (!isset($_FILES['xml_file']) || $_FILES['xml_file']['error'] !== UPLOAD_ERR_OK) {
            $this->json_output([
                'success' => false,
                'message' => 'Ошибка загрузки файла',
            ]);
        }

        $file = $_FILES['xml_file'];

        if ($file['size'] > 200 * 1024 * 1024) {
            $this->json_output([
                'success' => false,
                'message' => 'Файл слишком большой (лимит 200 МБ)',
            ]);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['xml', 'zip'];
        if (!in_array($ext, $allowedExt, true)) {
            $this->json_output([
                'success' => false,
                'message' => 'Разрешены только файлы XML или ZIP',
            ]);
        }

        // Сохранение файла
        $storageDir = rtrim($this->config->root_dir, '/')
            . '/files/terrorist_lists/' . $sourceCode;

        if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            $this->json_output([
                'success' => false,
                'message' => 'Не удалось создать директорию для хранения файлов',
            ]);
        }

        $storedFilename = sprintf(
            '%s_%s.%s',
            $sourceCode,
            date('Ymd_His'),
            $ext
        );
        $filePath = $storageDir . '/' . $storedFilename;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->json_output([
                'success' => false,
                'message' => 'Не удалось сохранить файл на диск',
            ]);
        }

        $fileHash = null;
        if (is_readable($filePath)) {
            $fileHash = hash_file('sha256', $filePath);
        }

        $uploadedBy = isset($this->manager->id) ? (int) $this->manager->id : null;

        try {
            $this->terroristImportFiles->addImportFile([
                'source_id'         => (int) $source->id,
                'original_filename' => $file['name'],
                'stored_filename'   => $storedFilename,
                'file_path'         => $filePath,
                'file_size'         => (int) $file['size'],
                'file_hash'         => $fileHash,
                'uploaded_by'       => $uploadedBy,
            ]);
        } catch (\Exception $e) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }

            $this->json_output([
                'success' => false,
                'message' => 'Ошибка сохранения записи о файле: ' . $e->getMessage(),
            ]);
        }

        $this->json_output([
            'success' => true,
            'message' => 'Файл успешно загружен и будет обработан кроном',
        ]);
    }

    /**
     * Список файлов по source_code
     */
    public function loadFiles(): void
    {
        $sourceCode = trim((string) $this->request->post('source_code'));
        if ($sourceCode === '') {
            $this->json_output([
                'success' => false,
                'message' => 'Не указан источник списка',
                'rows'    => [],
            ]);
        }

        $files = $this->terroristImportFiles->getFiles([
            'source_code' => $sourceCode,
            'limit'       => 100,
        ]);

        $this->json_output([
            'success' => true,
            'rows'    => $files,
        ]);
    }

    /**
     * Список клиентов (субъектов) по конкретному файлу
     */
    public function loadFileSubjects(): void
    {
        $fileId = (int) $this->request->post('file_id');

        if ($fileId <= 0) {
            $this->json_output([
                'success' => false,
                'message' => 'Не указан файл',
                'rows'    => [],
            ]);
        }

        $page  = (int) $this->request->post('page');
        $limit = (int) $this->request->post('limit');
        $query = trim((string) $this->request->post('query'));

        $page  = $page  > 0 ? $page  : 1;
        $limit = $limit > 0 ? $limit : 50;

        $filter = [
            'page'  => $page,
            'limit' => $limit,
            'query' => $query,
        ];

        $subjects = $this->terroristSubjectLists->getSubjects($fileId, $filter);
        $total    = $this->terroristSubjectLists->countSubjects($fileId, $filter);

        $this->json_output([
            'success' => true,
            'rows'    => $subjects,
            'file_id' => $fileId,
            'page'    => $page,
            'limit'   => $limit,
            'total'   => $total,
            'query'   => $query,
        ]);
    }

    /**
     * Актуальный список клиентов (is_current = 1) по всем источникам
     */
    public function loadCurrentSubjects(): void
    {
        $page  = (int) $this->request->post('page');
        $limit = (int) $this->request->post('limit');
        $query = trim((string) $this->request->post('query'));

        $page  = $page  > 0 ? $page  : 1;
        $limit = $limit > 0 ? $limit : 50;

        $filter = [
            'page'  => $page,
            'limit' => $limit,
            'query' => $query,
        ];

        $subjects = $this->terroristSubjects->getCurrents($filter);
        $total    = $this->terroristSubjects->countCurrent($filter);

        $this->json_output([
            'success' => true,
            'rows'    => $subjects,
            'page'    => $page,
            'limit'   => $limit,
            'total'   => $total,
            'query'   => $query,
        ]);
    }
}
