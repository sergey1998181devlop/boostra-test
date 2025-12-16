<?php

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();

$response = new StdClass();
$response->errors = array();

if ($action = $simpla->request->get('action', 'string'))
{
    switch ($action):
    
        case 'get_documents':
            $number = $simpla->request->get('number');
            $resp = $simpla->soap->get_statement_prolongation_base64($number);
        
            $response->documents = array();
            
            foreach ($resp as $key => $item)
            {
                $response->documents[] = array(
                    'file' => $simpla->config->root_url.'/files/contracts/'.$simpla->documents->save_pdf($item->{'ФайлBase64'}, $number, 'StatementProlongation'),
                    'name' => empty($key) ? 'Заявление о пролонгации договора микрозайма' : 'Документ',
                );
            }
            
        break;
    
    endswitch;
} else {
    $response->errors[] = 'empty_action';
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

echo json_encode($response);
