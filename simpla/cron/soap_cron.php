<?PHP

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once('/home/p/pravza/simpla/public_html/api/Simpla.php');

$simpla = new Simpla();

// Будем слать по 10, падает 1с wsdl в отказы
$zs =  $simpla->orders->get_orders(array('status'=>'0', 'limit'=>10));
ini_set('default_socket_timeout', 90);

if (!empty($zs))
{
	foreach ($zs as $z) {
		$user = $simpla->users->get_user(intval($z->user_id), true);

		// Так как в юзере могут быть изменения, как и в заявке,
		// Будем создавать новую заявку
		$zayavochka = new stdClass();

		$zayavochka->lastname = $user->lastname;
		$zayavochka->firstname = $user->firstname;
		$zayavochka->patronymic = $user->patronymic;
		$zayavochka->birth = !empty($user->birth) ? $user->birth : '01.01.1900';
		$zayavochka->phone_mobile = $user->phone_mobile;
		$zayavochka->email = $user->email;
		$zayavochka->passport_serial = $user->passport_serial;
		$zayavochka->passport_date = $user->passport_date;
		$zayavochka->subdivision_code = $user->subdivision_code;
		$zayavochka->passport_issued = $user->passport_issued;
		$zayavochka->Regregion = $user->Regregion;
		$zayavochka->Regdistrict = $user->Regdistrict;
		$zayavochka->Regcity = $user->Regcity;
		$zayavochka->Reglocality = $user->Reglocality;
		$zayavochka->Regstreet = $user->Regstreet;
		$zayavochka->Regbuilding = $user->Regbuilding;
		$zayavochka->Reghousing = $user->Reghousing;
		$zayavochka->Regroom = $user->Regroom;
		$zayavochka->Faktregion = $user->Faktregion;
		$zayavochka->Faktdistrict = $user->Faktdistrict;
		$zayavochka->Faktcity = $user->Faktcity;
		$zayavochka->Faktlocality = $user->Faktlocality;
		$zayavochka->Faktstreet = $user->Faktstreet;
		$zayavochka->Faktbuilding = $user->Faktbuilding;
		$zayavochka->Fakthousing = $user->Fakthousing;
		$zayavochka->Faktroom = $user->Faktroom;
		$zayavochka->site_id = $user->site_id;
		$zayavochka->partner_id = $user->partner_id;
		if(empty($user->partner_name))
			$zayavochka->partner_name = 'Boostra';
		else
			$zayavochka->partner_name = $user->partner_name;

		// Информация из базы по заявке
		$zayavochka->amount = intval($z->amount);
		$zayavochka->period = intval($z->period);
		$zayavochka->utm_source = $z->utm_source;
		$zayavochka->utm_medium = $z->utm_medium;
		$zayavochka->utm_campaign = $z->utm_campaign;
		$zayavochka->utm_content = $z->utm_content;
		$zayavochka->utm_term = $z->utm_term;
		$zayavochka->webmaster_id = $z->webmaster_id;
		$zayavochka->click_hash = $z->click_hash;

		// из 1с айди, если юзера туда уже отправили
		// наверное

		//Саша Кисляков, [27.03.19 20:14]
		//Пусто отправь

		//if(!empty($user->uid) && ($user->uid != 'Error'))
		//	$zayavochka->id = $user->uid;
		//else
			$zayavochka->id = '';

		// К машине еще не готовы
		$zayavochka->Car = '';

		if (!empty($zayavochka))
		{
			$return = $simpla->notify->soap_send_zayavka($zayavochka);
		}

		if(!empty($return->return->id_zayavka))
		{
			//print_r($return);
			$simpla->orders->update_order(intval($z->id), array('status'=>1, '1c_id'=>$return->return->id_zayavka));
		}
		
        if(!empty($return->return->id_uid) and empty($user->uid))
		{
			//print_r($return);
			$simpla->users->update_user(intval($user->id), array('1c_id'=>$return->return->id_uid));
		}
		elseif(!empty($return->return->Error))
		{
			// Пока не местные шлем в отказ, если чего потом достанем
			$simpla->orders->update_order(intval($z->id), array('status'=>3, 'note'=>strval($return->return->Error)));
		}

		unset($zayavochka);
	}
}

session_write_close();


?>