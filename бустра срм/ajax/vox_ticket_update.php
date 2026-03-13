<?php

session_start();
chdir('..');

require 'api/Simpla.php';

$response = array();
$simpla = new Simpla();
$data = $simpla->request->post();
header('Content-Type: application/json; charset=utf-8');

try {
    $realData = $data;
    $data = json_decode($data);
    
    if (array_key_exists('call_assigned_to_agent', $data->callbacks[0])){

        $dataNew = json_encode(['message' => $realData],JSON_UNESCAPED_UNICODE);

        $mch_api = curl_init();
        curl_setopt($mch_api, CURLOPT_URL, "https://bbatm0vu4l9fh8abbtaf.containers.yandexcloud.net/match");
        curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($mch_api, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
        curl_setopt($mch_api, CURLOPT_POST, true);
        curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($mch_api, CURLOPT_POSTFIELDS, $dataNew);
        $result = curl_exec($mch_api);


        echo 'success';
    }else{
        $data = $data->callbacks[0]->new_calls->calls[0];
        
        $callId = $data->id;

        $voximplant = new Voximplant();
        $ticketId = $voximplant->getTicketId($callId);

        $callDuration = $data->duration;
        $tags = "";
        foreach ($data->tags as $tag) {
            $tags .= $tag->tag_name . " ";
        }

        if (empty($tags)) {
            $tags = 'Empty tags';
        }

        $incoming = $data->is_incoming ? 'Входящий' : 'Исходящий';
        $phone = json_decode($data->call_calls);
        $caller = $phone[0]->remote_number == 'admin' ?  $phone[0]->local_number : $phone[1]->local_number;
        $operator = $phone[0]->remote_number == 'admin' ?  $phone[1]->local_number : $phone[0]->local_number ;
        $datetimeStart = date("Y-m-d H:i:s", strtotime($data->datetime_start));
        $datetimeEnd = date("Y-m-d H:i:s", (strtotime(date($datetimeStart)) + $data->duration));

        $comment = array(
            'api_token' => $simpla->config->usedesk_api,
            'message' => "<p>Тип: $incoming</p>
                  <p>ID: $data->id</p>
                  <p>Время звонка: $datetimeStart</p>
                  <p>Время завершения: $datetimeEnd</p>
                  <p>Продолжительность звонка: $data->duration с.</p>
                  <p>Оператор: $operator</p>
                  <p>Абонент: $caller</p>
                  <p>Тег: $tags</p><br>
                  <a href='$data->record_url' target='_blank'>Запись звонка</a>",
            'ticket_id' => $ticketId,
            'type' => 'private',
            'subject' => 'voximplant'
        );

       

        $mch_api = curl_init();
        curl_setopt($mch_api, CURLOPT_URL, 'https://api.usedesk.ru/create/comment');
        curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
        curl_setopt($mch_api, CURLOPT_POST, true);
        curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($mch_api, CURLOPT_POSTFIELDS, $comment);
        $result = curl_exec($mch_api);
        $txt = json_encode($data);
        file_put_contents('voximplant/calls.txt', "txt : $txt\n", FILE_APPEND);
        $vox_calls = new VoxCalls();

        $call = new stdClass();
        $call->id = $data->id ?? null;
        $call->call_result_code = $data->call_result_code ?? null;
        $call->call_cost = $data->call_cost ?? null;
        $call->datetime_start = !empty($data->datetime_start) ? date('Y-m-d H:i:s', strtotime($data->datetime_start)) : null;
        $call->duration = $data->duration ?? null;
        $call->is_incoming = $data->is_incoming ?? null;
        $call->phone_a = $caller ?? null;
        $call->phone_b = $operator ?? null;
        $call->scenario_id = $data->scenario_id ?? null;
        $call->tags = $data->tags ?? null;
        $call->record_url = $data->record_url ?? null;
        $call->user_id = $data->user_id ?? null;
        $call->queue_id = $data->queue_id ?? null;

        $callData = json_decode($data->call_data, true);
        if (isset($callData['assessment'])) {
            $call->assessment = $callData['assessment'];
        }

        $vox_calls->save($call);

        echo 'success';
    }


} catch (Exception $e) {
    print_r('4545454');
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
