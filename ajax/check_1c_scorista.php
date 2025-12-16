<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');


require_once 'api/Simpla.php';

$simpla = new Simpla();
$result = array();

$loan_number = $simpla->request->get('number');

$simpla->db->query("
    SELECT id FROM __orders WHERE 1c_id = ?
", (string)$loan_number);
if ($order_id = $simpla->db->result('id'))
{
    $resp = $simpla->orders->check_order_1c($loan_number);
    $stat = $resp->return->Скориста;
        
        if ($stat == 'Одобрено')
        {
            $order = $simpla->orders->get_order((int)$order_id);
            $simpla->orders->update_order($order_id, array('scorista_sms_sent'=>1));

            $sms = "Вам одобрен займ! boostra.ru";
            $simpla->notify->send_sms($order->phone_mobile, $sms);        

            $result['sent'] = 1;
        }
}
else
{
    $result['error'] = 'unfined_order';
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

echo json_encode($result);