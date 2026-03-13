<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$user_id = $simpla->request->get('user_id');

$balance = $simpla->users->get_user_balance(intval($user_id));

$utc_payment_date = strtotime($balance->payment_date);
$utc_now = strtotime(date('Y-m-d 00:00:00'));

if ($utc_now > $utc_payment_date)
{
    if (!empty($balance->zayavka)) {
        $status_zaim = $simpla->users->getZaimAspStatus($balance->zaim_number);
        $show_asp_modal = !$status_zaim;
        if ($show_asp_modal){
            echo true;
            exit();
        }
    }
}
echo false;
exit();
