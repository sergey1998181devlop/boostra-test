<?PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r'));
session_start();
require_once(dirname(__DIR__, 2) . '/api/Simpla.php');

$simpla = new Simpla();

$zs =  $simpla->users->get_users(array('ok_uid'=>'true', 'limit'=>300000));
 
$today = date('Y-m-d'); 
if (!empty($zs))
{
	foreach ($zs as $z) {
$user_balance_1c = $simpla->users->get_user_balance_1c($z->uid);
$user_balance_1c = $simpla->users->make_up_user_balance($z->id, $user_balance_1c->return);	
$user_balance = $simpla->users->get_user_balance($z->id);

			    			if(!$user_balance || empty($user_balance))
			    				$balance_id =$simpla->users->add_user_balance($user_balance_1c);
			    			else
			    				$balance_id = $simpla->users->update_user_balance($z->id, $user_balance_1c);
	}
}

session_write_close();


?>