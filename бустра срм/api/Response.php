<?php

require_once('Simpla.php');

/**
 * Class Response
 */
class Response extends Simpla
{
	public function __construct()
	{		
		parent::__construct();
	}

    /**
     * Возвращает данные в формате JSON
     * @param $data
     * @param int $statusCode
     * @return void
     */
    public function json_output($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     * Возвращает данные в формате HTML
     * @param $data
     * @return void
     */
    public function html_output($data)
    {
        header("Content-type: text/html; charset=UTF-8");
        echo $data;
        exit();
    }

    /**
     * Отдает файл в браузер
     * @param string $file_name
     * @param string $content_type
     * @param bool $delete_file
     * @return void
     */
    public function file_output(string $file_name, string $content_type, bool $delete_file = true)
    {
        header("Content-type: $content_type");
        header("Content-Disposition: attachment; filename=$file_name");
        header("Content-length: " . filesize($file_name));
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile("$file_name");

        if ($delete_file) {
            unlink($file_name);
        }
    }
};
