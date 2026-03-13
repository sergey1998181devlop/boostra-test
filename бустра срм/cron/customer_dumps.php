<?php
session_start();
chdir('..');

require 'api/Simpla.php';

if (!function_exists('config')) {
    require_once 'app/Core/Helpers/BaseHelper.php';
}

$response = array();
$simpla = new Simpla();
$data = $simpla->request->get('data');


$user = new Users();
$time = date("H");

if (($time > '20' && $time <= '23') || ($time == "00") || ($time >= "01" && $time < "08" )  ){
    exit();
}
$end = $time < 11 ? date("Y-m-d 0". ($time-1) .":59:59") : date("Y-m-d ". ($time-1) .":59:59");
$start = date("Y-m-d H:00:00",strtotime($end));

    file_put_contents('voximplant/voximplant.txt',"startTime : $end \n",FILE_APPEND);

    $users = $user->getUsersByDateInterval($start, $end);

    if ($users) {
        $data = [
            'rows' => json_encode($users),
            'campaign_id' => 685
        ];
        $domain = config('services.voximplant.domain', 'boostra2023');
        $token = config('services.voximplant.token', '');
        $apiUrl = rtrim(config('services.voximplant.api_url_v3', 'https://kitapi-ru.voximplant.com/api/v3'), '/');

        $mch_api = curl_init(); // initialize cURL connection
        curl_setopt($mch_api, CURLOPT_URL, $apiUrl . '/agentCampaigns/appendContacts?access_token=' . $token . '&domain=' . $domain);
        curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
        curl_setopt($mch_api, CURLOPT_POST, true);
        curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($mch_api, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($mch_api);

//        file_put_contents('voximplant/voximplant.txt',"result : $result \n", FILE_APPEND);
        echo 'success';
        exit();
}
exit();

