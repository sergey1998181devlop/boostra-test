<?PHP

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once('/home/p/pravza/simpla/public_html/api/Simpla.php');

$simpla = new Simpla();

$new_users =  $simpla->users->get_users(array('ok_uid'=>true, 'sort'=>'date', 'limit'=>5));



foreach ($new_users as $nu) {
	print_r($nu->uid);
	print_r('<br/>');

	$balance_1c = $simpla->users->get_user_balance_1c($nu->uid);
	$balance_1c = $balance_1c->return;

	$balance_1c_norm->user_id = $nu->id;
	$balance_1c_norm->zaim_number = $balance_1c->НомерЗайма;
	$balance_1c_norm->percent = $balance_1c->ПроцентнаяСтавка;
	$balance_1c_norm->ostatok_od = $balance_1c->ОстатокОД;
	$balance_1c_norm->ostatok_percents = $balance_1c->ОстатокПроцентов;
	$balance_1c_norm->ostatok_peni = $balance_1c->ОстатокПени;
	$balance_1c_norm->client = $balance_1c->Клиент;
	$balance_1c_norm->zaim_date = $balance_1c->ДатаЗайма;
	$balance_1c_norm->zayavka = $balance_1c->Заявка;

	$user_balance = $simpla->users->get_user_balance($nu->id);

	if(!$user_balance || empty($user_balance))
		$balance_id = $simpla->users->add_user_balance($balance_1c_norm);
	else
		$balance_id = $simpla->users->update_user_balance($nu->id, $balance_1c_norm);

	$new_user_balance = $simpla->users->get_user_balance($nu->id);

	print_r($new_user_balance);
	print_r('<br/><br/>');

	// [НомерЗайма] => Ошибка [ПроцентнаяСтавка] => 0 [ОстатокОД] => 0 [ОстатокПроцентов] => 0 [Клиент] => Не найден контрагент [Заявка] => [ДатаЗайма] => 0001-01-01T00:00:00 [ОстатокПени] => 0 [user_id] => 4435
}