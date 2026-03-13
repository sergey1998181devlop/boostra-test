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

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniquePart = bin2hex(random_bytes(8));

        $fileKey = "payments_rs/$orderId/" . $uniquePart . ($extension ? '.' . $extension : '');

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

            $insertOk = $this->db->query("
                INSERT INTO __payments_rs (user_id, order_id, contract_id, name, attachment)
                VALUES (?, ?, ?, ?, '')
            ", $userId, $orderId, $contractId, $fileName);

            if (!$insertOk) {
                throw new Exception('DB INSERT __payments_rs failed');
            }

            $paymentId = (int)$this->db->insert_id();
            if ($paymentId <= 0) {
                throw new Exception('DB INSERT __payments_rs returned empty insert_id');
            }

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

            $this->sendResponse(['error' => 'Не удалось загрузить чек. Попробуйте позже.'], 500);
        }

        try {
            $this->fileStorageService->putFile($filePath, $fileKey);

            $updateOk = $this->db->query("
                UPDATE __payments_rs
                SET attachment = ?
                WHERE id = ?
            ", $fileKey, $paymentId);

            if (!$updateOk) {
                throw new Exception('DB UPDATE __payments_rs attachment failed');
            }

            if ((int)$this->db->affected_rows() < 1) {
                throw new Exception('DB UPDATE __payments_rs affected_rows = 0');
            }

            $this->sendCommentTo1cOnUpload($orderId, $userId, $contractNumber, $fileKey);

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
            $this->sendResponse(['error' => 'Не удалось загрузить чек. Попробуйте позже.'], 500);
        }
    }

    /**
     * Отправляет в 1С комментарий о загрузке чека клиентом
     *
     * @param int $orderId
     * @param int $userId
     * @param string $contractNumber
     * @param string $attachmentKey
     * @return void
     */
    private function sendCommentTo1cOnUpload(int $orderId, int $userId, string $contractNumber, string $attachmentKey): void
    {
        try {
            $order = $this->orders->get_order($orderId);
            if (!$order || empty($order->id_1c)) {
                return;
            }

            $user = $this->users->get_user_uid($userId);
            if (!$user || empty($user->uid)) {
                return;
            }

            $created = date('Y-m-d H:i:s');
            $dateTimeFormatted = date('d.m.Y H:i', strtotime($created));
            $receiptLink = rtrim($this->config->back_url, '/') . '/ajax/view_payment_receipt.php?key=' . urlencode($attachmentKey);
            $text = 'Клиент загрузил чек об оплате по договору ' . $contractNumber . ' ' . $dateTimeFormatted . ".\n"
                . 'Ссылка на чек в CRM: ' . $receiptLink;

            $systemManager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

            $this->soap->sendComment([
                'number' => $order->id_1c,
                'user_uid' => $user->uid,
                'created' => $created,
                'text' => $text,
                'manager' => $systemManager->name_1c,
            ]);
        } catch (Exception $e) {
            $this->open_search_logger->create(
                "Ошибка отправки комментария о загрузке чека в 1С",
                [
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'attachment_key' => $attachmentKey,
                    'error' => $e->getMessage(),
                ],
                'upload_payment_rs_1c',
                \OpenSearchLogger::LOG_LEVEL_ERROR
            );
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
