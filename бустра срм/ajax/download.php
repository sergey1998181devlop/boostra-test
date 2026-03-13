<?php
error_reporting(0);
ini_set('display_errors', 'Off');
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

class DownloadDocument extends Simpla
{
    private $allowedPaths;

    public function __construct()
    {
        parent::__construct();
        $this->allowedPaths = [
            'c' => 'contracts',
            'up' => 'uploaded_files',
            'load' => 'load',
            'asp' => 'asp',
            'not_rep' => 'not_replaced',
            'rep' => 'doc',
        ];
        $this->run();
    }

    public function run()
    {
        if (empty($_SESSION['manager_id'])) {
            $this->json_response(403, 'Access to this file path is forbidden.');
        }
        $pattern = '/\/[^ ]*/';
        $type = preg_replace($pattern, '', $this->request->get('dataType'));
        $doc = preg_replace($pattern, '', $this->request->get('dataDoc'));
        $user = preg_replace($pattern, '', $this->request->get('dataUser'));
        $docType = preg_replace($pattern, '', $this->request->get('dataDocType'));
        $order = preg_replace($pattern, '', $this->request->get('dataOrder'));

        switch ($type) {
            case 'asp':
                $fileUrl = $this->config->front_url . '/files/' . $type . '/' . $doc . '.pdf';
                break;
            case 'c':
                $fileUrl = $this->config->front_url . '/files/' . $this->allowedPaths[$type] . '/' . $doc;
                break;
            case 'rep':
                $fileUrl = $this->config->front_url . '/files/' . $this->allowedPaths[$type] . '/doc_id_' . $doc . '.pdf';
                break;
            case 'load':
                $fileUrl = $this->config->root_url . '/order/' . $order . '?action=' . $type . '&uid=' . $doc . '&type=' . $docType;
                $this->download_from_1c($fileUrl);
                return;
            case 'up':
                $fileUrl = $this->config->root_url . '/files/' . $this->allowedPaths[$type] . '/' . $docType;
                $this->download_uploaded_doc($fileUrl);
                return;
            case 'not_rep':
                $fileUrl = $this->config->front_url . '/document/' . $user . "/" . $doc . '?from_crm=1';
                break;
            default:
                $this->json_response(400, 'Invalid data type.');
                return;
        }

        $this->download_file($fileUrl);
    }

    private function download_file($fileUrl)
    {
        $fileContent = $this->curl_get_content($fileUrl);

        if ($fileContent !== false) {
            $fileName = basename($fileUrl);
            if (!strstr($fileName, '.pdf')) {
                $fileName .= '.pdf';
            }
            $this->send_file($fileContent, $fileName);
            $this->response->json_output(['url' => $fileUrl]);
        } else {
            http_response_code(500);
            echo 'Failed to get file content: ' . curl_error($curl);
        }
    }

    private function curl_get_content($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $fileContent = curl_exec($curl);
        curl_close($curl);
        return $fileContent;
    }

    private function download_from_1c($fileUrl)
    {
        $queryString = parse_url($fileUrl, PHP_URL_QUERY);
        parse_str($queryString, $params);
        $file_storage = $this->filestorage->load_document($params['uid'], $params['type']);
        $fileData = file_get_contents(ROOT . $file_storage);
        $fileName = basename($file_storage);
        $this->send_file($fileData, $fileName);
        $this->response->json_output(['url' => $file_storage]);
    }

    private function download_uploaded_doc($fileUrl)
    {
        $fileData = file_get_contents(ROOT . $fileUrl);
        $fileName = basename($fileUrl);
        $this->send_file($fileData, $fileName);
        $this->response->json_output(['url' => $fileUrl]);
    }

    private function send_file($fileData, $fileName)
    {
        if ($fileData !== false) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . strlen($fileData));
        } else {
            http_response_code(500);
            echo 'Error downloading the file.';
        }
    }

    private function json_response($status_code, $message)
    {
        http_response_code($status_code);
        echo json_encode(['error' => $message]);
        exit;
    }
}

(new DownloadDocument());
