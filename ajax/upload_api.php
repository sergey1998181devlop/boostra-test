<?php
define('ROOT', dirname(__DIR__));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile'])) {

    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $originalFileName = $_FILES['pdfFile']['name'];
    $ext = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Недопустимый тип файла']);
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['pdfFile']['tmp_name']);
    $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    if (!in_array($mimeType, $allowedMimes, true)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Недопустимый MIME-тип']);
        exit;
    }

    $allowedTypes = ['doc', 'docs', 'certs', 'documents', 'images', 'contracts', 'reports'];
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    if (!in_array($type, $allowedTypes, true)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Недопустимый тип загрузки']);
        exit;
    }

    $targetDirectory = ROOT . '/files/' . $type . '/';
    // Encode the filename in UTF-8
    $utf8FileName = mb_convert_encoding($originalFileName, 'UTF-8', 'auto');
    if ($utf8FileName === ".pdf") {
        $utf8FileName = iconv(mb_detect_encoding($originalFileName), 'UTF-8', $originalFileName);
    }

// If both conversions fail, generate a new filename
    if ($utf8FileName === ".pdf") {
        $newFileName = uniqid() . '_' . $originalFileName;
        $utf8FileName = mb_convert_encoding($newFileName, 'UTF-8');
    }
    $utf8FileName = basename($utf8FileName);
    $targetFile = $targetDirectory . $utf8FileName;

    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0755, true);
    }
    if (file_exists($targetFile)) {
        @unlink($targetFile);
    }

    if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $targetFile)) {
        // File uploaded successfully
        $response = [
            'status' => 'success',
            'message' => 'File uploaded successfully',
            'file_path' => '/files/' . $type . '/' . $utf8FileName
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Failed to upload file
        $response = [
            'status' => 'error',
            'message' => 'Failed to upload file'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
