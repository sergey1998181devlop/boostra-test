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

$targetDirectory = ROOT . '/files/uploaded_files/';
$originalFileName = $_FILES['image']['name'];
$key = $_POST['key'];

if (empty($key) || $key != 'article-image') {
  $simpla->response->json_output(['error' => 'Доступ запрещен']);
}

if (!in_array( strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION)) ,['jpeg','jpg','png'] )) {
    $simpla->response->json_output(['error' => 'Неверный тип файла. Доступные типы .jpeg,.jpg,.png']);
} else {
    $newFileName = strtotime("now") . "." . strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    $targetFile = $targetDirectory . $newFileName;

    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    if (file_exists($targetFile)) {
        $simpla->response->json_output(['error' => 'Файл с данным именем уже существует. Переименуйте файл или воспользуйтесь кнопкой Заменить']);
    } else {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
          $simpla->response->json_output(
            [
              'success' => 'success',
              'url' => $simpla->config->back_url . "/files/uploaded_files/" . $newFileName,
            ]
          );
        }
    }
}
?>
