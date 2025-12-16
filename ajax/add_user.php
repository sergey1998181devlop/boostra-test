<?php
session_start();
require_once('../api/Simpla.php');
$simpla = new Simpla();



$user =	array(

	'lastname' => 'lastname',
	'firstname' => 'firstname',
	'patronymic' => 'patronymic',
	'birth' => 'birthday',
	'phone_mobile' => '79372046907',
	'email' => 'email2',

	'passportCode' => 'passportCode',
	'passportDate' => 'passportDate',
	'subdivisionCode' => 'subdivisionCode',
	'passportWho' => 'passportWho',

	'Regregion' => 'Regregion',
	'Regcity' => 'Regcity',
	'Regstreet' => 'Regstreet',
	'Reghousing' => 'Reghousing',
	'Regbuilding' => 'Regbuilding',
	'Regroom' => 'Regroom',

	'Faktregion' => 'Faktregion',
	'Faktcity' => 'Faktcity',
	'Faktstreet' => 'Faktstreet',
	'Fakthousing' => 'Fakthousing',
	'Faktbuilding' => 'Faktbuilding',
	'Faktroom' => 'Faktroom',

	'site_id' => 'Boostra',
	'partner_id' => 'partner_id',
	'partner_name' => 'partner_name',

	'enabled'=> 1

	);


$query = $simpla->db->placehold("SELECT count(*) as count FROM __users WHERE phone_mobile=?", $user['phone_mobile']);
$simpla->db->query($query);

$test = $simpla->request->post('test');

print_r($test);
print_r('<br/><br/>');

$test = $simpla->db->result('test');

print_r($users_count);
print_r('<br/><br/>');

//$simpla->db->query('SELECT id FROM __users WHERE phone_mobile=?', $user['phone_mobile']);
//$user_id = $simpla->db->result('id');

//print_r($user_id);

print_r('<br/><br/>');

print_r($user);

$user_id = $simpla->users->add_user($user);

print_r('<br/><br/>');

print_r($user_id);