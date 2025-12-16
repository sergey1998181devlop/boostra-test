<?php
define('ROOT', dirname(__DIR__));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile'])) {

    $targetDirectory = ROOT . '/files/'.$_POST['type'].'/';
    $originalFileName = $_FILES['pdfFile']['name'];
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
    $targetFile = $targetDirectory . $utf8FileName;

    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }
    if (file_exists($targetFile)) {
        @unlink($targetFile);
    }

    if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $targetFile)) {
        // File uploaded successfully
        $response = [
            'status' => 'success',
            'message' => 'File uploaded successfully',
            'file_path' => $targetFile
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
