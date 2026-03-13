<?PHP
error_reporting(-1);
ini_set('display_errors', 'On');

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
//header('Expires: '.date('r'));
session_start();

require_once(realpath(__DIR__).'/../../api/Simpla.php');

$simpla = new Simpla();

$simpla->db->query("
    UPDATE     __transactions
    SET sended = 2
    WHERE DATE(created) < NOW() - INTERVAL 7 DAY
    AND sended = 0
    AND (
        status != 'REJECTED'
        AND status != 'DEADLINE_EXPIRED'
    )

");
$results = $simpla->db->results();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';


$query = $simpla->db->placehold("
    SELECT *
    FROM __transactions AS t
    WHERE payment_type = 'debt' 
    AND payment_id != 0        
    AND 
    (
        (
            t.status != 'CONFIRMED'
            AND t.status != 'REJECTED'
            AND t.status != 'DEADLINE_EXPIRED'
            AND sended = 0
        )
        OR 
        (
            t.status = 'CONFIRMED'
            AND sended = 0
        )
    )
    ORDER BY id DESC
    LIMIT 10000
");
$simpla->db->query($query);

if ($results = $simpla->db->results())
{
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';

    $simpla->transactions->update_transactions_state($results);
}



/** 
 Старый код
 
$zs =  $simpla->payment->get_payments(array('status'=>'0', 'limit'=>15));
 
ini_set('default_socket_timeout', 90);

 
 if (!empty($zs))
{
	
	foreach ($zs as $z) {
		$user = $simpla->users->get_user(intval($z->user_id));
 
		 $payment = new \stdClass();
		$payment->Сумма = $z->summ*100;
		$payment->Телефон = $user->phone_mobile;
		$payment->Дата = $z->payment_date;
	//	$payment->УИД = $user->uid;
		//$payment->PaymentId = $z->id;
 	//print_r($payment);
if (!empty($payment))
		{
		 	$return = $simpla->notify->soap_send_oplata($payment);
		}
 	//print_r($return);
 	  if(!empty($return))
		{

 	$simpla->payment->update_payment(intval($z->id), array('status'=>1));
		}   
	 
	}
} 

 
**/

