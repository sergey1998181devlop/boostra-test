<?php
error_reporting(0);
ini_set('display_errors', 'Off');
date_default_timezone_set('Europe/Moscow');

header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();
$user = $_POST['user'] ?? '';
$order = $_POST['order'] ?? '';

$targetDirectory = ROOT . '/files/uploaded_files/';

$originalFileName = $_FILES['pdfFile']['name'];

// Encode the filename in UTF-8
$utf8FileName = mb_convert_encoding($originalFileName, 'UTF-8', 'auto');

if (!in_array( strtolower(pathinfo($utf8FileName, PATHINFO_EXTENSION)) ,['pdf','doc','docx'] )) {
    $simpla->response->json_output(['error' => 'Неверный тип файла. Доступные типы .pdf,.doc,.docx']);
} else {
    if ($order != '') {
        $utf8FileName = "Заявка {$order}. " . $utf8FileName;
    }

    $targetFile = $targetDirectory . $utf8FileName;

    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    if (file_exists($targetFile)) {
        $simpla->response->json_output(['error' => 'Файл с данным именем уже существует. Переименуйте файл или воспользуйтесь кнопкой Заменить']);
    } else {
        if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $targetFile)) {

            $apiUrl = $simpla->config->front_url . '/ajax/upload_api.php';
            $fileData = new CURLFile($targetFile, 'application/pdf', $utf8FileName);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['pdfFile' => $fileData,  'type' => 'uploaded_files']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $responseData = json_decode($response, true);
            if ($responseData && $responseData['status'] === 'success') {
                $simpla->documents->upload_document(
                    [
                        'name' => $utf8FileName,
                        'user_id' => (int)$user,
                        'order_id' => (int)$order
                    ]
                );
                $simpla->response->json_output(['success' => 'success']);
            } else {
                $simpla->response->json_output(['error' => 'Failed to upload file to boostra']);
            }
        }
    }



}


?>
