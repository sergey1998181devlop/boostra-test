<?PHP

	require_once('../../api/Simpla.php');
	$simpla = new Simpla();

	$user = $simpla->users->get_user(807);

	$user->oders = $simpla->orders->get_orders(array('user_id'=>$user->id));

	//print_r($user);

	//print_r('<br/><br/>');

	// Возьмем у юзера order такой-то, 17303 напримр
	// Оплата

	$payment = new stdClass;
	//$payment->
	

	$total_order = 1000;

	//print_r('Сумма запроса - '.$total_order.'<br/><br/>');

	$payment_method_id = 14;

	$payment_method = $simpla->payment->get_payment_method($payment_method_id);

	//print_r($payment_method);

	//print_r('<br/><br/>');

	//print_r('<br/><br/>'.$payment_method->module);

	$module_name = $payment_method->module;

	$form = '';
	$add_card_form = '';
	if(!empty($module_name) && is_file("../../payment/$module_name/$module_name.php"))
	{
		include_once("../../payment/$module_name/$module_name.php");
		$module = new $module_name();
		//print_r($module);
		// id order'a
		//$form = $module->checkout_form(17303 , 'Оплатить');
		$add_card_form = $module->card_form(807);
	}
	print_r('<br/><br/>');
	print_r($form);
	print_r('<br/><br/>');
	print_r($add_card_form);	
