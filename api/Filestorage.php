<?php

require_once 'Simpla.php';

class Filestorage extends Simpla
{
    private $dir = 'files/contracts/documents/';

    private $storage_url = 'http://storage.boostra.ru/files/';

    public function __construct()
    {
        parent::__construct();

    }

    public function load_file($file_uid, $s3_name = null)
    {
        // проверяем есть ли такой уид уже
        $query = $this->db->placehold("
            SELECT * 
            FROM __files
            WHERE storage_uid = ?
        ", (string)$file_uid);
        $this->db->query($query);

        if ($exist_file = $this->db->result()) {
            $filename = $this->getFilePath($exist_file->name);
            if (file_exists($filename) && filesize($filename) > 0)
                return 'file_exists';
        }

        $ch = curl_init();

        $headers = array();

        curl_setopt($ch, CURLOPT_URL, $this->storage_url . $file_uid);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid headers
                return $len;

            $headers[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        }
        );
        $file_content = curl_exec($ch);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($file_content);echo '</pre><hr />';
        curl_close($ch);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($headers);echo '</pre><hr />';        
        if (isset($headers['content-disposition'])) {
            $expl = array_map('trim', explode(';', $headers['content-disposition']));
            $filename_arr = explode('=', $expl[1]);

            $storage_filename = $filename_arr[1];

//echo $storage_filename.'<br />';
            if (!empty($storage_filename)) {
                if (empty($exist_file)) {
                    $query = $this->db->query("
                        SELECT id, name 
                        FROM __files 
                        WHERE name IN (?@)
                    ", [
                        $storage_filename,
                        $storage_filename.'.jpg',
                        $storage_filename.'.jpeg',
                        $storage_filename.'.png',
                        $storage_filename.'.jp2',
                    ]);
                    if ($found = $this->db->result()) {
                        file_put_contents($this->getFilePath($found->name), $file_content);
                        $update_data = [
                          'storage_uid' => $file_uid,
                        ];

                        if (!empty($s3_name)) {
                            $update_data['s3_name'] = $s3_name;
                        }

                        $this->users->update_file($found->id, $update_data);
                        return 'updated';
                    }
                } else {
                    file_put_contents($this->getFilePath($exist_file->name), $file_content);
                    return 'loaded';
                }

            }
        }

        return null;
    }

    public function upload_file($file, $timeout = 9)
    {
        if (!file_exists($file)) {
            $this->logError("File does not exist: $file");
            return false;
        }

        $filesize = $this->getFileSize($file);
        $url = $this->buildUploadUrl($file, $filesize);

        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => file_get_contents($file),
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

        return substr($response, $header_size);
    }

    private function getFileSize($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            $headers = get_headers($file, true);
            return isset($headers['Content-Length']) ? (int) $headers['Content-Length'] : 0;
        }

        return filesize($file);
    }

    private function buildUploadUrl($file, $filesize)
    {
        $queryParams = http_build_query([
            'filename' => basename($file),
            'filesize' => $filesize,
        ]);
        return "http://storage.boostra.ru/files/uploadbinary?" . $queryParams;
    }

    private function logError($message)
    {
        error_log($message);
    }


    public function getStorageUrl()
    {
        return $this->storage_url;
    }

    public function load_document($file_uid)
    {
        if (file_exists($this->config->root_dir . $this->dir . $file_uid . '.pdf'))
            return $this->config->root_url . '/' . $this->dir . $file_uid . '.pdf';


        $ch = curl_init();

        $headers = array();

        curl_setopt($ch, CURLOPT_URL, $this->storage_url . $file_uid);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid headers
                return $len;

            $headers[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        }
        );
        $file_content = curl_exec($ch);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($file_content);echo '</pre><hr />';
        curl_close($ch);

        if (isset($headers['content-disposition'])) {
            $expl = array_map('trim', explode(';', $headers['content-disposition']));
            $filename_arr = explode('=', $expl[1]);

            $storage_filename = $filename_arr[1];

//echo '<br />'.$storage_filename.'<br />';
            if (!empty($storage_filename)) {
                file_put_contents($this->config->root_dir . $this->dir . $file_uid . '.pdf', $file_content);
                return $this->config->root_url . '/' . $this->dir . $file_uid . '.pdf';
            }
        }

        return null;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFilePath(string $file_name): string
    {
        return $this->config->root_dir . $this->config->original_images_dir . $file_name;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFileUrl(string $file_name): string
    {
        return $this->config->root_url . '/' . $this->config->original_images_dir . $file_name;
    }

    public function getDir()
    {
        return $this->config->root_dir . $this->dir;
    }

}