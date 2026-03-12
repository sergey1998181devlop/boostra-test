<?php
$token = $_POST['token'];
$file = $_FILES['file'];

$allowed_extensions = [
    'zip',
    'rar',
    'txt',
    'csv',
    'pdf',
    'xls',
    'xlsx',
    'odt',
    'doc',
    'rtf',
    'docx',
    'png',
    'gif',
    'jpeg',
    'jpg',
];

if ($token !== '3f0a7d75a8cb5d4f3c5a76a22d55a4608a0c77a6b6e8d2c4b6d7a8e5a3f0e1a3') {
    http_response_code(403);
    exit('Недопустимый токен.');
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Ошибка при загрузке файла: ' . $file['error']);
}

$uploadDirectory = dirname(__DIR__) . '/files/docs/';
$filename = basename($file['name']);
$destinationPath = $uploadDirectory . $filename;

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if(!in_array($ext, $allowed_extensions)){
    http_response_code(403);
    exit('Не верное расширение файла.');
}

if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
    $relativePath = $filename;
    $response = array('success' => true, 'url' => $relativePath);
    echo json_encode($response);
} else {
    http_response_code(500);
    exit('Ошибка при сохранении файла.');
}
