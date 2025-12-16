<?php
require_once('../api/Simpla.php');
$simpla = new Simpla();

$token = $simpla->request->post('token');
$user_id = $simpla->request->post('user_id');
$one = $simpla->request->post('one');

$result = array();
if ($token != 'Bstr_163_load_docs_Mobile') {
    $result['error'] = 1;
} else {
    $user_balance = $simpla->users->get_user_balance(intval($user_id));
    $docs = array();

    if (!empty($user_balance->zaim_number) && $user_balance->zaim_number != 'Нет открытых договоров' && $user_balance->zaim_number != 'Нет открытых договоров')
    {
        $result['success'] = 1;
        if ($one) {
            $simpla->filestorage->load_document($one);
        } elseif ($docs = $simpla->soap->get_documents($user_balance->zaim_number)) {
            foreach ($docs as $doc) {
                $simpla->filestorage->load_document($doc->УИДХранилища);
            }
        } else {
            $result['success'] = 0;
        }
    }
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		
echo json_encode($result);