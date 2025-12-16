<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
session_start();

require_once 'api/Simpla.php';

$response = array();
$simpla = new Simpla();

$number = $simpla->request->get('number');
if (empty($number))
{
    $response['error'] = 'empty contract number';
}
else
{
    if (file_exists($simpla->config->root_dir.'files/contracts/Application/Application_'.$number.'.pdf'))
        unlink($simpla->config->root_dir.'files/contracts/Application/Application_'.$number.'.pdf');
    if (file_exists($simpla->config->root_dir.'files/contracts/Consent/Consent_'.$number.'.pdf'))
        unlink($simpla->config->root_dir.'files/contracts/Consent/Consent_'.$number.'.pdf');
    if (file_exists($simpla->config->root_dir.'files/contracts/Contract/Contract_'.$number.'.pdf'))
        unlink($simpla->config->root_dir.'files/contracts/Contract/Contract_'.$number.'.pdf');
    if (file_exists($simpla->config->root_dir.'files/contracts/Other/Other_'.$number.'.pdf'))
        unlink($simpla->config->root_dir.'files/contracts/Other/Other_'.$number.'.pdf');
    
    $docs = $simpla->soap->get_contract($number);
    if (empty($docs))
        $response['error'] = 'notfound';
    else
    {
        $response = $docs;
    }
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

echo json_encode($response);