<?php

session_start();
chdir(__DIR__ . '/..');
require_once 'api/Simpla.php';

$simpla = new Simpla();

$order_id = $simpla->request->get('order_id', 'integer');
if (!$order_id) {
    header('HTTP/1.0 400 Bad Request');
    exit('Не указан номер заявки');
}

if (!$simpla->getManagerId()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Доступ запрещён');
}

$order = $simpla->orders->get_order($order_id);
if (!$order) {
    header('HTTP/1.0 404 Not Found');
    exit('Заявка не найдена');
}

$raw = $simpla->order_data->read($order_id, 'self_employee_document');
if ($raw === null || $raw === '') {
    header('HTTP/1.0 404 Not Found');
    exit('Документ не найден');
}

$file_data = json_decode($raw, true);
if (!is_array($file_data)) {
    header('HTTP/1.0 404 Not Found');
    exit('Некорректные данные документа');
}

$file_key = $file_data['path'] ?? $file_data['storage_uid'] ?? null;
if ($file_key === null || $file_key === '') {
    header('HTTP/1.0 404 Not Found');
    exit('Некорректные данные документа');
}
$filename = $file_data['name'] ?? 'podtverzhdenie_tcelevogo_zajma.pdf';

try {
    $storage = new \App\Service\FileStorageService(
        $simpla->config->s3['endpoint'],
        $simpla->config->s3['region'],
        $simpla->config->s3['key'],
        $simpla->config->s3['secret'],
        $simpla->config->s3['Bucket']
    );
    $content = $storage->downloadFile($file_key);
} catch (Exception $e) {
    $simpla->logging('download_self_employee_document', '', ['order_id' => $order_id], ['error' => $e->getMessage()], 'download_self_employee_document_errors.txt');
    header('HTTP/1.0 502 Bad Gateway');
    exit('Ошибка при загрузке файла из хранилища');
}

if ($content === '') {
    header('HTTP/1.0 404 Not Found');
    exit('Файл не найден в хранилище');
}

$safe_name = preg_replace('/[^\p{L}\p{N}\s_\-\.]/u', '_', $filename);
if ($safe_name === '') {
    $safe_name = 'document.pdf';
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $safe_name . '"');
header('Content-Length: ' . strlen($content));
header('Cache-Control: private, no-cache');
echo $content;
exit;
