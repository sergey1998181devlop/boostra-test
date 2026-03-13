<?php

session_start();
chdir('..');
date_default_timezone_set('Europe/Moscow');
require_once dirname(__FILE__) . '/../api/Simpla.php';
$simpla = new Simpla();

$start = date('Y-m-d H:i:00', strtotime(' - 20 minutes'));
$end = date('Y-m-d H:i:59', strtotime(' - 10 minutes'));
$startVox = date('Y-m-d 00:00:00');
$endVox = date('Y-m-d 23:59:59');

if (date('H:i') == "10:00") {
    $start = date('Y-m-d 21:40:00', strtotime("-1 days"));
    $end = date('Y-m-d 09:30:59');
    $startVox = date('Y-m-d 00:00:00', strtotime("-1 days"));
    $endVox = date('Y-m-d 23:59:59', strtotime("-1 days"));
}
$ordersList = $simpla->orders->getApprovedOrders($start, $end);
$getSentData = $simpla->voximplant->getSentData($startVox, $endVox);

foreach ($ordersList as $key => $item) {
    foreach ($getSentData as $row) {
        if ($item->order_id == $row->order_id) {
            unset($ordersList[$key]);
        }
    }
}
if (!empty($ordersList)){
    $simpla->voximplant->addApprovedToVox($ordersList);
    $data = [
        "campaign_id" => 59678,
        'rows' => json_encode($ordersList),
    ];

    $simpla->voximplant->sendRobocompany($data);
}

exit();




