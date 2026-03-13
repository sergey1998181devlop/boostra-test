<?PHP
date_default_timezone_set('Europe/Samara');
sleep(5);

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r', time()));
session_start();
require_once('/home/p/pravza/simpla/public_html/api/Simpla.php');

$simpla = new Simpla();


// Будем брать всех юзеров, что есть в личном кабинете и выдавать им связанные уникальные айди в 1с
// По этим айди в дальнейшем будет работать платежная система с привязкой карт, а так-же баланс из 1с в личный кабинет,
// Плюс связь платежа и человека
$new_users =  $simpla->users->get_users(array('no_uid'=>true, 'sort'=>'date', 'limit'=>15));

ini_set('default_socket_timeout', 90);

foreach ($new_users as $nu) {
	
	$return = $simpla->notify->soap_get_uid($nu);
	
	if(!empty($return->return))
	{
//		if ($return->return != 'Error')
            $simpla->users->update_user(intval($nu->id), array('UID_status'=>'ok', 'UID'=>$return->return));
	}
	elseif(!empty($return->faultcode))
	{
		$simpla->users->update_user(intval($nu->id), array('UID_status'=>$return->faultcode.$return->faultstring, 'UID'=>'error'));
	}
	
}



session_write_close();


?>