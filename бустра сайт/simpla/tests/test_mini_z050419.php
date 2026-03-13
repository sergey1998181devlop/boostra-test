<?PHP

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once('../../api/Simpla.php');

$simpla = new Simpla();

ini_set('default_socket_timeout', 90);

// Так как в юзере могут быть изменения, как и в заявке,
// Будем создавать новую заявку
$zayavochka = new stdClass();

$zayavochka = new stdClass();

$zayavochka->lastname = 'Тест';
$zayavochka->firstname = 'Тест';
$zayavochka->patronymic = 'Тест';
$zayavochka->birth = '';
$zayavochka->phone_mobile = '+7 937 204 69 07';
$zayavochka->email = '';
$zayavochka->passport_serial = '';
$zayavochka->passport_date = '';
$zayavochka->subdivision_code = '';
$zayavochka->passport_issued = '';
$zayavochka->Regregion = 'Самарская';
$zayavochka->Regdistrict = '';
$zayavochka->Regcity = 'Самара';
$zayavochka->Reglocality = '';
$zayavochka->Regstreet = '';
$zayavochka->Regbuilding = '';
$zayavochka->Reghousing = '';
$zayavochka->Regroom = '';
$zayavochka->Faktregion = '';
$zayavochka->Faktdistrict = '';
$zayavochka->Faktcity = '';
$zayavochka->Faktlocality = '';
$zayavochka->Faktstreet = '';
$zayavochka->Faktbuilding = '';
$zayavochka->Fakthousing = '';
$zayavochka->Faktroom = '';
$zayavochka->site_id = '';

$zayavochka->partner_id = '';

	$zayavochka->partner_name = 'Boostra';


		// Информация из базы по заявке
$zayavochka->amount = intval('10000');
$zayavochka->period = intval('12');
$zayavochka->utm_source = '';
$zayavochka->utm_medium = '';
$zayavochka->utm_campaign = '';
$zayavochka->utm_content = '';
$zayavochka->utm_term = '';
$zayavochka->webmaster_id = '';
$zayavochka->click_hash = '';

$zayavochka->id = '';

// К машине еще не готовы
$zayavochka->Car = '';

print_r($zayavochka);
print_r('<br/><br/>');

if (!empty($zayavochka))
{
	$return = $simpla->notify->soap_send_zayavka($zayavochka);
}

print_r($return);
print_r('<br/><br/><hr/>');
