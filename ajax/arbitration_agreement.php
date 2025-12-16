<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$user_id = $simpla->request->get('user_id');

$balance = $simpla->users->get_user_balance(intval($user_id));

if (is_null($balance) || empty($balance->zaim_number)) {
    echo false;
    exit;
}

$document = $simpla->documents->getDocument(Documents::ARBITRATION_AGREEMENT, $balance->zaim_number);

$utc_payment_date = strtotime($balance->payment_date);
$utc_now = strtotime(date('Y-m-d 00:00:00'));

if ($utc_payment_date > 0 && $utc_now > $utc_payment_date) {
    if (empty($document->id) && preg_match('/A\d{2}-\d+/', $balance->zaim_number) === 1) {
        echo true;
        exit;
    }
}
echo false;
exit;
