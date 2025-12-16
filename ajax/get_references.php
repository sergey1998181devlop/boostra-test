<?php
error_reporting(0);
ini_set('display_errors', 'On');
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

class GetReferences extends Simpla
{
    public function __construct()
    {
        parent::__construct();

    }
    public function run()
    {
        $response = array();
        $loanId = trim($_GET['loanID'] ?? '');
        $referenceType = trim($_GET['referenceType'] ?? '');
        $referenceType = (in_array($referenceType, ['SPRAVKA_O_ZADOLZHENNOSTI', 'SPRAVKA_O_ZAKRITII'])) ? $referenceType : '';

        if ($loanId && $referenceType) {
            $request = [
                'НомерЗайма' => $loanId,
                'ВидСправки' => $referenceType,
            ];
            try {
                $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebSignal.1cws?wsdl");
                $response = $uid_client->__soapCall('ReferenceClose', array($request));
                $response = (array) $response;
            } catch (Exception $fault) {
                $response = (array) $fault;

            }
//            echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
//            var_dump($request, $response);
//            echo '</pre><hr />';
            $responseToLog = array();
            $responseToLog['return'] = substr($response['return'] ?? '',50);
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebSignal.1cws?wsdl ReferenceClose', (array)$request, $responseToLog, 'soap.txt');
        }

        if (($response['return']) && (strlen($response['return']) > 10)) {
            if ($response['return'] == 'false') {
                $response['error'] = "По данному номеру заявки справа не может быть выдана";
            } else {
                $response['success'] = 'ok';
            }
        } else {
            $response['status'] = 'error';
            $response['error'] = "Справка не может быть выдана";
        }
        echo json_encode($response);exit;
    }

}

$doc = new GetReferences();
$doc->run();
