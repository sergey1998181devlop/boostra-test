<?php

require_once('api/AmoCrm.php');

$client = new AmoCrm();

if ($_GET['code'] == 'amo_shmamo') {
    switch ($_GET['action']) {
        case 'add':
            $name = $_GET['name'];
            $phone = $_GET['phone'];

            echo $client->send_lead($name, $phone);
            break;

        case 'change':
            $amo_id = $_GET['amo_id'];
            $status_id = $_GET['status_id'];
            $pipeline_id = $_GET['pipeline_id'];

            echo $client->change_step($amo_id, $status_id, $pipeline_id);
            break;

        case 'get_token_from_code':
            echo $client->get_token_from_code($_GET['amo_code']);
            break;

        case 'refresh_token':
            echo $client->refresh_token();
            break;

        default:
            # code...
            break;
    }
}
