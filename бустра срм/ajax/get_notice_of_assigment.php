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

class GetNoticeOfAssigment extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        $response = array();
        $loanId = trim($_GET['loanID'] ?? '');
        if ($loanId) {
            $response = $this->soap->NoticeOfAssignment($loanId);
            if (!isset($response['error'])) {
                $response['return'] = json_decode($response['return'], true);
                if (!empty($response['return']['File'])) {
                    $response['success'] = 'ok';
                } else {
                    $response['status'] = 'error';
                    $response['error'] = "Данные не получены";
                }
            }
        }

        echo json_encode($response);
        exit();
    }

}

$doc = new GetNoticeOfAssigment();
$doc->run();
