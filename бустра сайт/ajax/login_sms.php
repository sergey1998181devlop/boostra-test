<?php
	require_once('../api/Simpla.php');
	$simpla = new Simpla();

	// Получим телефон
	$phone = $simpla->request->post('phone');



	// Повторная смс?
	//$repeat = $simpla->request->get('repeat', 'boolean');
	//print_r($repeat);
	//$repeat = false;

	if ( ! session_id() ) @ session_start();

	//if (!isset($_SESSION['sms']) || $repeat) {
	if ($_SESSION['sms_count'] < 30) {
		$_SESSION['login_sms'] = mt_rand(1000, 9999);
		$_SESSION['phone'] = $phone; // На всякий случай тут запомним
		if(isset($_SESSION['sms_count']))
			$_SESSION['sms_count']++;
		else
			$_SESSION['sms_count'] = 1;
		$result = $simpla->notify->send_sms($phone, $_SESSION['sms']);
	}
	//}

	header("Content-type: application/json; charset=UTF-8");
	header("Cache-Control: must-revalidate");
	header("Pragma: no-cache");
	header("Expires: -1");		
	print json_encode($result);