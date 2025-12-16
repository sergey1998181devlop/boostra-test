<?PHP

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once('../../api/Simpla.php');

//print_r(123);


$simpla = new Simpla();


$phone = '+7 (937) 204-69-07';
print_r($phone);
print_r('<br/><br/>');

$user = $simpla->users->get_user($phone);
print_r($user);

$_SESSION['user_id'] = $user->id;