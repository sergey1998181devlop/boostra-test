<?PHP

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once('../../api/Simpla.php');

$simpla = new Simpla();

if(!isset($stat_z_client))
	$stat_z_client = new SoapClient("http://88.99.22.210/work/ws/WebOtvetZayavki.1cws?wsdl");  

$user_id = 807;
//$user = $simpla->users->get_user(807);

$orders = $simpla->orders->get_orders(array('user_id'=>$user_id));

foreach($orders as &$o)
{
	$z = new stdClass();
	$z->НомерЗаявки = $o->id_1c;
	$returnnnn = $stat_z_client->__soapCall('GetOtvetZayavki',array($z));
	$o->stat = $returnnnn->return->Статус;

	$o->comment = $returnnnn->return->Комментарий;
	print_r($o->id_1c.'<br/>'.$o->stat.'<br/>'.$o->comment.'<br/><br/>');
}

//print_r($orders);
//$z = new stdClass();
//$z->НомерЗаявки = '000040601';

//$returnnnn = $stat_z_client->__soapCall('GetOtvetZayavki',array($z));

//print_r($returnnnn);

//$stat = $returnnnn->return->Статус;
//$comment = $returnnnn->return->Комментарий;

//print_r($stat);

/*
public function soap_send_zayavka($z)
{
    	// Функция отправки заявки в 1с

		// Очистим телефон от лишних символов
	$replace = array('+','(',')',' ','-');
	$z->phone_mobile = str_replace($replace,'',$z->phone_mobile);

	if(!isset($client))
		$client = new SoapClient("http://88.99.22.210/work/ws/WebZayavki.1cws?wsdl");  

	$returned = $client->__soapCall('GetZayavki',array($z));
	return $returned;
}
*/