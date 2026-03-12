<?php

use api\services\DocumentStorageService;

require_once('../api/Simpla.php');

class DocumentUploader extends Simpla
{
    const TOKEN = 'If0kkGeHe2EGzDFCx79DogWpqrFUUXfWF3T0Ki5czzOie4dY86hc0IXhVDyRe1cl';

    private DocumentStorageService $documentService;

    public function __construct()
    {
        parent::__construct();

        $token = $this->request->post('token', 'string');
        if (!$token || $token !== self::TOKEN) {
            $this->response->error('Access denied', 403);
        }

        $this->documentService = new DocumentStorageService();

        $action = $this->request->post('action', 'string');
        $contracts = $this->request->post('contract-numbers');
        $documentType = $this->request->post('document-type', 'string');

        $response = [];

        switch ($action) {
            case 'get_s3_links':
                $response = $this->getS3Keys($contracts, $documentType);
                break;
        }

        $this->response->success($response);
    }

    public function getS3Keys(array $contractNumbers, string $documentType): array
    {
        $results = [];

        foreach ($contractNumbers as $contractNumber) {
            $results[$contractNumber] = $this->documentService->processDocument($contractNumber, $documentType);
        }

        return $results;
    }
}

new DocumentUploader();
