<?php

namespace ajax;
require_once(dirname(__DIR__) . '/api/Simpla.php');

use api\services\FileStorageService;
use Exception;
use Simpla;

class UploadBkiQuestionHandler extends Simpla {
    private FileStorageService $fileStorageService;

    public function __construct() {
        parent::__construct();

        $this->fileStorageService = new FileStorageService(
            $this->config->BKI_STORAGE['endpoint'],
            $this->config->BKI_STORAGE['region'],
            $this->config->BKI_STORAGE['key'],
            $this->config->BKI_STORAGE['secret'],
            $this->config->BKI_STORAGE['bucket']
        );

        $this->handle();
    }

    private function handle(): void {
        try {
            $orderId = $this->request->post('order_id', 'integer');
            $userId = $this->request->post('user_id', 'integer');
            $contractNumber = $this->request->post('contract_number', 'string');
            $description = $this->request->post('problem_description', 'string');
            $files = $_FILES['bki_files'] ?? null;

            if (!$orderId || !$userId || !$contractNumber || !$description) {
                throw new Exception('Все поля обязательны для заполнения');
            }

            if (!$files || empty($files['name'][0])) {
                throw new Exception('Прикрепите хотя бы один файл');
            }

            $this->db->query("SELECT id FROM __contracts WHERE order_id = ? AND number = ? LIMIT 1", $orderId, $contractNumber);
            $contractId = $this->db->result('id');

            if (!$contractId) {
                throw new Exception('Договор не найден');
            }

            $this->db->query("SELECT created_at FROM __bki_questions WHERE user_id = ? AND order_id = ? ORDER BY created_at DESC LIMIT 1", $userId, $orderId);
            $lastCreated = $this->db->result('created_at');

            if ($lastCreated) {
                $daysPassed = floor((time() - strtotime($lastCreated)) / (60 * 60 * 24));
                $daysLeft = 12 - $daysPassed;

                if ($daysPassed < 12) {
                    throw new Exception("Вы ранее уже отправляли запрос. Следующий запрос может быть через {$daysLeft} дн.");
                }
            }

            $allowedTypes = ['image/png', 'image/jpeg', 'image/heif', 'image/heic'];
            $maxFileSize = 20 * 1024 * 1024;
            $processedFiles = 0;

            foreach ($files['tmp_name'] as $index => $tmpName) {
                if (!is_uploaded_file($tmpName)) {
                    continue;
                }

                $type = $files['type'][$index];
                $size = $files['size'][$index];
                $originalName = basename($files['name'][$index]);

                if (!in_array($type, $allowedTypes) || $size > $maxFileSize) {
                    continue;
                }

                $fileKey = "bki_questions/$orderId/" . uniqid() . '_' . $originalName;

                $this->fileStorageService->putFile($tmpName, $fileKey);

                $this->db->query(
                    "INSERT INTO __bki_questions 
                    (user_id, order_id, contract_id, name, attachment, description) 
                    VALUES (?, ?, ?, ?, ?, ?)",
                    $userId, $orderId, $contractId, $originalName, $fileKey, $description
                );

                $processedFiles++;
            }

            if ($processedFiles === 0) {
                throw new Exception('Ни один файл не был обработан');
            }

            echo json_encode(['success' => true, 'processed' => $processedFiles]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit;
    }
}

new UploadBkiQuestionHandler();