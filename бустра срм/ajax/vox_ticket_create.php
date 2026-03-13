<?php

session_start();
chdir('..');

require 'api/Simpla.php';

$response = array();
$simpla = new Simpla();
$data = $simpla->request->get('data');

$data['api_token'] = $simpla->config->usedesk_api;

try {
    $mch_api = curl_init(); // initialize cURL connection
    curl_setopt($mch_api, CURLOPT_URL, 'https://api.usedesk.ru/create/ticket');
    curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
    curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
    curl_setopt($mch_api, CURLOPT_POST, true);
    curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($mch_api, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($mch_api);


    $voximplant = new Voximplant();
    $voximplant->addTicketId($result, $data);

    $vox_calls = new VoxCalls();
    $call = new stdClass();
    $call->call_result_code = $data['result_code'];
    $call->phone_a = $data['phone_a'];
    $call->phone_b = $data['phone_b'];
    $call->id = $data['vox_call_id'];

    if (isset($data['datetime_start'])) {
        $call->datetime_start = $data['datetime_start'];
    }
    if (isset($data['duration'])) {
        $call->duration = $data['duration'];
    }
    if (isset($data['is_incoming'])) {
        $call->is_incoming = $data['is_incoming'];
    }
    if (isset($data['scenario_id'])) {
        $call->scenario_id = $data['scenario_id'];
    }
    if (isset($data['tags'])) {
        $call->tags = $data['tags'];
    }
    if (isset($data['call_cost'])) {
        $call->call_cost = $data['call_cost'];
    }
    if (isset($data['user_id'])) {
        $call->user_id = $data['user_id'];
    }
    if (isset($data['queue_id'])) {
        $call->queue_id = $data['queue_id'];
    }
    if (isset($data['record_url'])) {
        $call->record_url = $data['record_url'];
    }

    $callData = json_decode($data['call_data'], true);
    if (isset($callData['assessment'])) {
        $call->assessment = $callData['assessment'];
    }

    $vox_calls->save($call);


    echo 'success';

} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
