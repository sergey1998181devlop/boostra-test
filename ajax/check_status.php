<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');


require_once 'api/Simpla.php';

$simpla = new Simpla();
$result = array();


$order_id = $simpla->request->get('order_id', 'integer');
$order_status = $simpla->request->get('order_status', 'integer');

$query = $simpla->db->placehold("
    SELECT status
    FROM __orders
    WHERE id = ?
", $order_id);
$simpla->db->query($query);
$current_status = $simpla->db->result('status');

if (!empty($current_status))
{
    if ($current_status != $order_status)
    {
        $result['change'] = 1;
        
        if ($current_status == 10) {
            $simpla->db->query("
                SELECT * FROM b2p_p2pcredits
                WHERE order_id = ?
                AND likezaim_enabled = 1
            ", $order_id);
            $p2pcredit = $simpla->db->result();
            $simpla->likezaim->transfer($p2pcredit);
        }
    }
}
else
{
    $result['error'] = 'undefined_order';
}
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

echo json_encode($result);
exit;