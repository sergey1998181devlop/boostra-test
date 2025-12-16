<?
/**
 * Скорее всего файл не используется
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('/home/p/pravza/simpla/public_html/api/Simpla.php');	
$simpla = new Simpla();
	$payment = array('user_id'=>$_POST['customer'], 'summ'=>$_POST['amount'],'payment_date'=>date("Y-m-d H:i:s"),'payment_method_id'=>14,'order_id'=>$_POST['order_id']);
	 
		$query = $simpla->db->placehold("INSERT INTO __payments SET ?%", $payment);

		$simpla->db->query($query);
		$orderId = $simpla->db->insert_id();
		echo $orderId;