<?php

namespace ajax;
require_once(dirname(__DIR__) . '/api/Simpla.php');
date_default_timezone_set('Europe/Moscow');

use api\services\FileStorageService;
use Exception;
use Simpla;

class UploadPaymentRsHandler extends Simpla
{
    private FileStorageService $fileStorageService;

    public function __construct()
    {
        parent::__construct();

        $this->fileStorageService = new FileStorageService(
            $this->config->PAYMENTS_RS_STORAGE['endpoint'],
            $this->config->PAYMENTS_RS_STORAGE['region'],
            $this->config->PAYMENTS_RS_STORAGE['key'],
            $this->config->PAYMENTS_RS_STORAGE['secret'],
            $this->config->PAYMENTS_RS_STORAGE['bucket']
        );

        $this->handle();
    }

    private function handle(): void
    {
        $orderId = $this->request->post('order_id', 'integer');
        $userId = $this->request->post('user_id', 'integer');
        $contractNumber = $this->request->post('contract_number', 'string');

        if (!$orderId) {
            $this->sendResponse(['error' => 'Missing order_id.']);
        }

        $file = $this->request->files('rs_file');
        if (!$file || empty($file['tmp_name'])) {
            $this->sendResponse(['error' => 'Файл не загружен.']);
        }

        $allowedTypes = ['image/png', 'image/jpeg', 'image/heif', 'image/heic', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->sendResponse(['error' => 'Неподдерживаемый формат файла.']);
        }

        $maxFileSize = 100 * 1024 * 1024;
        if ($file['size'] > $maxFileSize) {
            $this->sendResponse(['error' => 'Размер файла превышает 100 МБ.']);
        }

        $filePath = $file['tmp_name'];
        $fileName = basename($file['name']);
        $fileKey = "payments_rs/$orderId/" . uniqid() . '_' . $fileName;

        $this->db->query("
            SELECT id FROM __contracts WHERE order_id = ? AND number = ?
        ", $orderId, $contractNumber);
        $contractId = $this->db->result('id');

        if (!$contractId) {
            $this->sendResponse(['error' => 'Договор не найден.']);
        }

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        $paymentId = null;

        try {
            $this->db->begin_transaction();

            $this->db->query("
                SELECT id FROM __payments_rs 
                WHERE contract_id = ? 
                  AND created_at BETWEEN ? AND ?
                  AND status IN ('new', 'approved')
                LIMIT 1
                FOR UPDATE
            ", $contractId, $todayStart, $todayEnd);

            if ($this->db->result('id')) {
                $this->db->rollback();
                $this->sendResponse(['error' => 'Вы уже загружали файл по этому договору сегодня. Повторная загрузка невозможна.']);
            }

            $this->db->query("
                INSERT INTO __payments_rs (user_id, order_id, contract_id, name, attachment)
                VALUES (?, ?, ?, ?, '')
            ", $userId, $orderId, $contractId, $fileName);

            $paymentId = $this->db->insert_id();
            $this->db->commit();

        } catch (Exception $e) {
            try {
                $this->db->rollback();
            } catch (Exception $rollbackException) {
                $this->logging(
                    __METHOD__,
                    '',
                    ['error' => 'Failed to rollback transaction', 'original_error' => $e->getMessage()],
                    ['rollback_error' => $rollbackException->getMessage()],
                    'upload_payment_rs_errors.txt'
                );
            }

            $this->sendResponse(['error' => 'Ошибка при сохранении файла: ' . $e->getMessage()], 500);
        }

        try {
            $this->fileStorageService->putFile($filePath, $fileKey);

            $this->db->query("
                UPDATE __payments_rs
                SET attachment = ?
                WHERE id = ?
            ", $fileKey, $paymentId);

            $this->sendResponse(['success' => true, 'file_key' => $fileKey], 200);
        } catch (Exception $e) {
            $this->db->query("DELETE FROM __payments_rs WHERE id = ?", $paymentId);

            try {
                $this->fileStorageService->deleteFile($fileKey);
            } catch (Exception $deleteException) {
                $this->logging(
                    __METHOD__,
                    '',
                    ['error' => 'Failed to delete file after upload failure', 'file_key' => $fileKey],
                    ['delete_error' => $deleteException->getMessage()],
                    'upload_payment_rs_errors.txt'
                );
            }

            $this->sendResponse(['error' => 'Ошибка при загрузке файла: ' . $e->getMessage()], 500);
        }
    }

    private function sendResponse(array $data, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}

new UploadPaymentRsHandler();
