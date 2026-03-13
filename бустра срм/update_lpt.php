<?php

require_once 'api/Lpt.php';
require_once 'api/Simpla.php';

$lpt = new Lpt();

if (isset($_GET['id']) AND isset($_GET['text'])) {
    if ($_GET['code'] == 'Yy9dfh_8') {
        $lpt->update_item($_GET['id'], [
            'comment' => htmlentities($_GET['text'])
        ]);

        echo '{"result": "succuses"}';
    } else {
        echo '{"result": "error"}';
    }
} else {
    $lpt->change_lpt_status($_GET['id'], $_GET['status']);
    //$lpt->change_lpt_status($_GET['id'], $_GET['status']);

    header("Location: http://" . $_SERVER['HTTP_HOST'] . "/tasks_overdue");
}




