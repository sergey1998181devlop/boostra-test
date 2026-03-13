<?PHP

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once('../../api/Simpla.php');

//print_r(123);


$simpla = new Simpla();



/*$client = new SoapClient("http://88.99.22.210/work/ws/WebZayavki.1cws?wsdl", array("trace" => 1));  

print_r('<br/><br/>');
print_r($client->__getLastRequestHeaders());


print_r('<br/><br/>');
print_r($client->__getLastRequest());

print_r($client->__getFunctions());  

print_r('<br/><br/>');

print_r($client->__getTypes());  

print_r('<br/><br/>');

*/

$z2 = new stdClass;
$z2->lastname = "test_lastname";
$z2->firstname = "test_firstname";
$z2->patronymic = "test_patronymic";

$z2->birth = "22.01.1990";
$z2->phone_mobile = "79372046907";
$z2->email = "test_email";

$z2->passport_serial = "test_passport_serial";
$z2->passport_date = "10.02.2010";
$z2->subdivision_code = "test_subdivision_code";
$z2->passport_issued = "test_passport_issued";

$z2->Regregion = "test_Regregion";
$z2->Regdistrict = "test_Regdistrict";
$z2->Regcity = "test_Regcity";
$z2->Reglocality = "test_Reglocality";
$z2->Regstreet = "test_Regstreet";
$z2->Regbuilding = "test_Regbuilding";
$z2->Reghousing = "test_Reghousing";
$z2->Regroom = "test_Regroom";
$z2->Faktregion = "test_Faktregion";
$z2->Faktdistrict = "test_Faktdistrict";
$z2->Faktcity = "test_Faktcity";
$z2->Faktlocality = "test_Faktlocality";
$z2->Faktstreet = "test_Faktstreet";
$z2->Faktbuilding = "test_Faktbuilding";
$z2->Fakthousing = "test_Fakthousing";
$z2->Faktroom = "test_Faktroom";
$z2->site_id = "test_site_id";
$z2->partner_id = "test_partner_id";
$z2->partner_name = "test_partner_name";


$returned = $simpla->notify->soap_send_zayavka($z2);
//print_r($returned);