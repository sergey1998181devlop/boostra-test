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
$order_1c_status = $simpla->request->get('order_1c_status', 'string');

$query = $simpla->db->placehold("
    SELECT status, 1c_status
    FROM __orders
    WHERE id = ?
", $order_id);
$simpla->db->query($query);
$order = $simpla->db->result();
$current_status = $order->status ?? null;
$current_1c_status = $order->{'1c_status'} ?? '';

if (!empty($current_status))
{
    $crm_status_changed = $current_status != $order_status;
    $status_1c_changed = $current_1c_status != $order_1c_status;

    if ($crm_status_changed || $status_1c_changed) {
        $result['change'] = 1;

        if ($crm_status_changed && $current_status == 10) {
            $simpla->db->query("
                SELECT * FROM b2p_p2pcredits
                WHERE order_id = ?
                AND likezaim_enabled = 1
            ", $order_id);
            $p2pcredit = $simpla->db->result();
            if (!empty($p2pcredit) && is_object($p2pcredit)) {
                $simpla->likezaim->transfer($p2pcredit);
            }
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