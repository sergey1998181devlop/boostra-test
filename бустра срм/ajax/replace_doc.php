<?php

date_default_timezone_set('Europe/Moscow');

header('Content-type: application/json; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();

class ReplaceDocument extends Simpla
{
    public function __construct()
    {
        parent::__construct();

    }

    public function run()
    {
        $uid = $_POST['uid'] ?? '';
        $docType = $_POST['docType'] ?? '';

        if ($docType == 'asp_zaim') {
            $this->replaceAspZaim($uid, $_FILES['pdfFile']['tmp_name']);

        } elseif ($docType == 'document') {
            $this->replaceDoc($uid, $_FILES['pdfFile']['tmp_name']);
        } elseif ($docType == 'uploaded_files') {
            $user = $_POST['user'] ?? '';
            $order = $_POST['order'] ?? '';
            $tpmFile = $_POST['fileName'];
            $name = $_POST['name'];
            $this->replaceUploadedDoc($user, $order, $_FILES['pdfFile']['tmp_name'],$tpmFile,$name);
        } else {
            $type = $_POST['type'] ?? '';
            $zaim = $_POST['zaim'] ?? '';
            $this->replaceDoc1C($type, $uid, $zaim);
        }


    }

    public function replaceAspZaim($zaim_number, $file)
    {

        if (!file_exists(ROOT . '/files/asp/')) {
            mkdir(ROOT . '/files/asp/', 0777, true);
        }

        if (move_uploaded_file($file, ROOT . '/files/asp/' . 'asp_zaim_' . $zaim_number . '.pdf')) {
            $filePath = ROOT . '/files/asp/' . 'asp_zaim_' . $zaim_number . '.pdf';
            $this->uploadApi($filePath, 'asp');
            $this->response->json_output(['success' => 'success']);
        }

    }

    public function replaceDoc($doc_id, $file)
    {

        if (!file_exists(ROOT . '/files/doc/')) {
            mkdir(ROOT . '/files/doc/', 0777, true);
        }
        if (move_uploaded_file($file, ROOT . '/files/doc/' . 'doc_id_' . $doc_id . '.pdf')) {
            $this->documents->update_document($doc_id, ['replaced' => true]);
            $filePath = ROOT . '/files/doc/' . 'doc_id_' . $doc_id . '.pdf';
            $this->uploadApi($filePath, 'doc');
            $this->response->json_output(['success' => 'success']);
        }
    }

    public function replaceUploadedDoc($user, $order, $file, $tmpFile,$name)
    {
        if (!file_exists(ROOT . '/files/uploaded_files/')) {
            mkdir(ROOT . '/files/doc/', 0777, true);
        }

        if (!in_array( strtolower(pathinfo($name, PATHINFO_EXTENSION)) ,['pdf','doc','docx'] )) {
            $this->response->json_output(['error' => 'Неверный тип файла. Доступные типы .pdf,.doc,.docx']);
        } else {
            if ($order != '') {
                $name = "Заявка {$order}. " . $name;
            }
            if (move_uploaded_file($file, ROOT . '/files/uploaded_files/' . $name)) {
                if (file_exists(ROOT . '/files/uploaded_files/' . $tmpFile)) {
                    @unlink(ROOT . '/files/uploaded_files/' . $tmpFile);
                }
                $this->documents->update_uploaded_document($name, $user, $order,$tmpFile);
                $filePath = ROOT . '/files/uploaded_files/' . $name;
                $this->uploadApi($filePath, 'uploaded_files');
                $this->response->json_output(['success' => 'success']);
            }
        }



    }

    public function replaceDoc1C($type, $uid, $zaim)
    {
        $targetDirectory = ROOT . '/files/uploaded_files/' . $type;

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], ROOT . '/files/uploaded_files/' . "$type/$uid.pdf")) {
            $filePath = ROOT . '/files/uploaded_files/' . "$type/$uid.pdf";
            $filesize = filesize($filePath);
            header('Content-Length: ' . $filesize);

            $storage_uid = $this->filestorage->upload_file($filePath, $filesize);
            $this->soap->DocumentEditing($zaim, $type, 'Замена', $storage_uid);
            $this->response->json_output(['success' => 'success']);
        }
    }

    public function uploadApi($filePath, $type)
    {
        $apiUrl = $this->config->front_url . '/ajax/upload_api.php';

        $fileData = new CURLFile($filePath, 'application/pdf', basename($filePath));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['pdfFile' => $fileData, 'type' => $type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        if ($responseData && $responseData['status'] === 'success') {
            $this->response->json_output(['success' => 'success']);
        } else {
            $this->response->json_output(['error' => 'Failed to upload file to boostra']);
        }
    }
}

$doc = new ReplaceDocument();
$doc->run();
