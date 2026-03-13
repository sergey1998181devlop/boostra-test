<?php

use App\Service\FileStorageService;

require_once __DIR__ . '/../api/Simpla.php';

class ViewPaymentReceipt extends Simpla
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }
    
    private function run()
    {
        $fileKey = $this->request->get('key');
        
        if (empty($fileKey)) {
            header('HTTP/1.0 400 Bad Request');
            exit('File key is required');
        }

        $this->db->query("SELECT name FROM __payments_rs WHERE attachment = ? LIMIT 1", $fileKey);
        $payment = $this->db->result();

        if (!$payment) {
            header('HTTP/1.0 404 Not Found');
            exit('Receipt not found');
        }
        
        try {
            $fileStorageService = new FileStorageService(
                $this->config->PAYMENTS_RS_STORAGE['endpoint'],
                $this->config->PAYMENTS_RS_STORAGE['region'],
                $this->config->PAYMENTS_RS_STORAGE['key'],
                $this->config->PAYMENTS_RS_STORAGE['secret'],
                $this->config->PAYMENTS_RS_STORAGE['bucket']
            );

            $fileContent = $fileStorageService->downloadFile($fileKey);
            
            if (empty($fileContent)) {
                header('HTTP/1.0 404 Not Found');
                exit('File not found in storage');
            }

            $extension = strtolower(pathinfo($fileKey, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                'bmp' => 'image/bmp',
                'webp' => 'image/webp'
            ];
            
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . $payment->name . '"');
            header('Content-Length: ' . strlen($fileContent));
            header('Cache-Control: public, max-age=31536000');
            
            echo $fileContent;
            exit;
            
        } catch (Exception $e) {
            error_log('Error viewing payment receipt: ' . $e->getMessage());
            header('HTTP/1.0 500 Internal Server Error');
            exit('Error loading file: ' . $e->getMessage());
        }
    }
}

new ViewPaymentReceipt();

