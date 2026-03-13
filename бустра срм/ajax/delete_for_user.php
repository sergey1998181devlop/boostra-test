<?php

error_reporting(0);
ini_set('display_errors', 'Off');

header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
define('ROOT', dirname(__DIR__));
session_start();
chdir('..');

require 'api/Simpla.php';


class DeleteForUsers extends Simpla
{
    const TABLE_1 = 's_documents';
    const TABLE_2 = 's_asp_to_zaim';
    const TABLE_3 = '1C';
    const TABLE_4 = 's_uploaded_documents';


    const TABLE_DATA = [
        1 => self::TABLE_1,
        2 => self::TABLE_2,
        3 => self::TABLE_3,
        4 => self::TABLE_4
    ];

    public function __construct()
    {
        parent::__construct();

        $this->run();


    }

    public function run()
    {
        $id = $this->request->post('id');
        $table_name = $this->request->post('table');
        $table_url = self::TABLE_DATA[$table_name];
        if ($table_name == 4) {
            $doc = $this->documents->get_uploaded_document_by_id($id);
            if ($doc) {
                $nameFile = $doc->name;
                if (file_exists(ROOT . '/files/uploaded_files/' . $nameFile)) {
                    @unlink(ROOT . '/files/uploaded_files/' . $nameFile);
                }
            }

        }
        if ($table_name != 3) {
            $this->documents->deleteDocument($id, $table_url);
        } else {
            $zaim = $this->request->post('zaim');
            $type = $this->request->post('type');
            $this->soap->DocumentEditing($zaim, $type, 'Удаление', "");
        }

    }


}

(new DeleteForUsers());
echo 'success';
