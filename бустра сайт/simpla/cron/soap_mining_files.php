<?PHP
date_default_timezone_set('Europe/Samara');

header("Content-Type: text/html; charset=utf-8");
header('Cache-Control: no-store, no-cache');
header('Expires: '.date('r', time()));
session_start();
require_once('/home/p/pravza/simpla/public_html/api/Simpla.php');

$simpla = new Simpla();


$simpla->db->query("
    SELECT DISTINCT user_id 
    FROM __files 
    WHERE status = 1
");

foreach ($simpla->db->results('user_id')  as $user_id)
{
    $simpla->notify->soap_mining_files($user_id);
}


session_write_close();


?>