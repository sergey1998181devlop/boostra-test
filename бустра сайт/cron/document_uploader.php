<?php

use api\services\DocumentStorageService;

require_once dirname(__DIR__) . '/api/Simpla.php';

/**
 * Класс генериррует pdf документы арбитражных соглашений и ШКД.
 * Выгружает в s3 хранилище
 */
class DocumentUploaderCron extends Simpla
{
    private DocumentStorageService $documentService;

    private array $documentTypes = [
       Documents::ARBITRATION_AGREEMENT,
       Documents::PENALTY_CREDIT_DOCTOR,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->documentService = new DocumentStorageService();
    }

    public function run(): void
    {
        $documents = $this->documents->get_documents([
            'type' => $this->documentTypes,
            'filestorage_uid' => ''
        ]);

        if (empty($documents)) {
            echo "Нет документов для обработки.\n";
            return;
        }

        foreach ($documents as $document) {
            echo "Загружаю {$document->number}...\n";
            $s3Key = $this->documentService->processDocument($document->contract_number, $document->type);

            if ($s3Key) {
                $this->documents->update_document($document->id, ['filestorage_uid' => $s3Key]);
                echo "Документ {$document->contract_number} загружен: {$s3Key} \n";
            } else {
                echo "Ошибка: не полчилось загрузить документ {$document->contract_number}\n";
            }
        }
    }
}

$cron = new DocumentUploaderCron();
$cron->run();
