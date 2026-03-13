<?php

require_once 'Simpla.php';

class Filestorage extends Simpla
{
    private $dir;
    private $storage_url;
    private $doc_storage_url;

    public function __construct($dir = 'voximplant/', $storage_url = 'http://storage.bm.boostra.ru/files/', $doc_storage_url = 'http://storage.boostra.ru/files/')
    {
        parent::__construct();
        $this->dir = $dir;
        $this->storage_url = $storage_url;
        $this->doc_storage_url = $doc_storage_url;
    }

    public function load_file($file_uid): ?string
    {
        return $this->fetch_file($this->doc_storage_url . $file_uid);
    }

    public function upload_file($file, $filesize = 0)
    {
        $headers = get_headers($file, true);
        $filesize = isset($headers['Content-Length']) ? (int)$headers['Content-Length'] : $filesize;

        $curl_options = [
            CURLOPT_URL => 'http://storage.boostra.ru/files/uploadbinary?filename=' . basename($file) . '&filesize=' . $filesize,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 9,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => file_get_contents($file),
            CURLOPT_HTTPHEADER => ['Content-Type: application/octet-stream'],
        ];

        return $this->curl_request($curl_options);
    }

    /**
     * @param $fileData
     * @param $fileName
     * @param $timeout
     * @return false|string
     */
    public function upload_file_like_client($fileData, $fileName, $timeout = 9)
    {

        $filesize = strlen($fileData);
        $url = $this->buildUploadUrl($fileName, $filesize);

        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fileData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/octet-stream',
            ],
        ];

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $this->logError("cURL Error: " . curl_error($curl));
            return false;
        }

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpcode !== 201) {
            $this->logError("Failed to upload file. HTTP Code: $httpcode. Response: $response");
            return false;
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        curl_close($curl);

        $fileId = substr($response, $header_size);
        return $this->doc_storage_url . $fileId;
    }

    private function getFileSize($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            $headers = get_headers($file, true);
            return isset($headers['Content-Length']) ? (int) $headers['Content-Length'] : 0;
        }

        return filesize($file);
    }

    /**
     * @param $fileName
     * @param $filesize
     * @return string
     */
    private function buildUploadUrl($fileName, $filesize): string
    {
        $queryParams = http_build_query([
            'filename' => $fileName,
            'filesize' => $filesize,
        ]);
        return "http://storage.boostra.ru/files/uploadbinary?" . $queryParams;
    }

    /**
     * @param $message
     * @return void
     */
    private function logError($message)
    {
        error_log($message);
    }


    /**
     * @param $file_uid
     * @param $type
     * @return string|null
     */
    public function load_document($file_uid, $type): ?string
    {
        return $this->fetch_file($this->storage_url . $file_uid, $file_uid, $type);
    }

    /**
     * @param $url
     * @param $file_uid
     * @param $type
     * @return string|null
     */
    private function fetch_file($url, $file_uid = '', $type = ''): ?string
    {
        $ch = curl_init();
        $headers = [];

        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) return $len;
                $headers[strtolower(trim($header[0]))] = trim($header[1]);
                return $len;
            },
        ];

        curl_setopt_array($ch, $curl_options);
        $file_content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("CURL Error in fetch_file: " . $error);
            return null;
        }

        if (isset($headers['content-disposition'])) {
            $expl = array_map('trim', explode(';', $headers['content-disposition']));
            $filename_arr = explode('=', $expl[1]);
            if (!empty($filename_arr[1])) {
                $file_path = $this->config->root_dir . $this->dir . $file_uid . '/' . $type . '.pdf';
                file_put_contents($file_path, $file_content);
                return $this->config->root_url . '/' . $this->dir . $file_uid . '/' . $type . '.pdf';
            }
        }

        return null;
    }

    /**
     * @param $options
     * @return false|string
     */
    private function curl_request($options)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('CURL error: ' . curl_error($ch));
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // Логгирование HTTP кода ответа
        error_log('HTTP Response Code: ' . $httpcode);

        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 300) {
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            return $body;
        }

        return false;
    }
}
