<?php

namespace api\services;

use Simpla;

class DocumentStorageService extends Simpla
{
    private FileStorageService $storage;
    private string $s3BasePath;

    public function __construct()
    {
        parent::__construct();

        $this->s3BasePath = 'as_docs';

        $this->storage = new FileStorageService(
            $this->config->s3['endpoint'],
            $this->config->s3['region'],
            $this->config->s3['key'],
            $this->config->s3['secret'],
            $this->config->s3['Bucket']
        );
    }

    public function processDocument(string $contractNumber, string $documentType): ?string
    {
        try {
            $document = $this->documents->get_documents_by_contract(
                ['contract_number' => $contractNumber, 'type' => $documentType, 'limit' => 1]
            );

            $contract = $this->contracts->get_contract_by_params(['number' => $contractNumber]);

            if (!$document || !$contract) {
                return false;
            }

            if ($document->filestorage_uid && $this->storage->fileExists($document->filestorage_uid)) {
                return $document->filestorage_uid;
            }

            $pdfFilePath = $this->docs->generateDocumentPdfToFile($contractNumber, $documentType);

            $s3Key = $this->generateS3Key($contract);
            $this->storage->putFile($pdfFilePath, $s3Key);

            if (file_exists($pdfFilePath)) {
                unlink($pdfFilePath);
            }

            $this->documents->update_document($document->id, [
                'filestorage_uid' => $s3Key,
            ]);

            return $s3Key;

        } catch (Exception $e) {
            $this->logging(
                __METHOD__,
                'Error',
                ['contract' => $contractNumber, 'type' => $documentType],
                ['error' => $e->getMessage()],
                'documents_to_s3_errors.log'
            );
            return false;
        }
    }

    /**
     * Генерирует путь до файла
     * @param $contract
     * @return string
     * @throws \Exception
     */
    private function generateS3Key($contract): string
    {
        $contractYearPath = explode('-', $contract->number)[0];
        $contractDate = date('Ymd', strtotime($contract->create_date));
        $filename = substr(bin2hex(random_bytes(4)), 0, 6);

        return "{$this->s3BasePath}/{$contractYearPath}/{$contractDate}/{$contract->number}/{$filename}.pdf";
    }
}
