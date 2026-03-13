<?php

declare(strict_types=1);

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

date_default_timezone_set('Europe/Moscow');

chdir('..');

require_once 'api/Simpla.php';

if (!function_exists('config')) {
    require_once 'app/Core/Helpers/BaseHelper.php';
}

$simpla = new Simpla();

/**
 * Send API
 *
 * @param array $data
 * @param string $domain
 * @param string $token
 * @param string $method
 * @return array
 */
function send(array $data, string $domain, string $token, string $method = 'appendToCampaign'): array
{
    $url = config('services.voximplant.api_url_v3', 'https://kitapi-ru.voximplant.com/api/v3') . '/outbound/';

    $data["domain"] = $domain;
    $data["access_token"] = $token;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'PHP-MCAPI/2.0',
        CURLOPT_POST => 1,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => $url . $method,
        CURLOPT_POSTFIELDS => http_build_query($data),
    ));

    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}

$organizationId = (int)$simpla->request->post('organization_id');
$voxCredentials = Voximplant::getVoxConfigForOrganization($organizationId);

if (empty($voxCredentials) || empty($voxCredentials['domain']) || empty($voxCredentials['token'])) {
    $voxCredentials = array(
        'domain' => config('services.voximplant.domain', 'boostra2023'),
        'token' => config('services.voximplant.token', ''),
    );
}

$voxDomain = $voxCredentials['domain'];
$voxToken = $voxCredentials['token'];

$days1St = date('Y-m-d 00:00:00', strtotime('+1 day'));
$days1Fn = date('Y-m-d 23:59:59', strtotime('+1 day'));

$days2St = date('Y-m-d 00:00:00', strtotime('+2 day'));
$days2Fn = date('Y-m-d 23:59:59', strtotime('+2 day'));

$days3St = date('Y-m-d 00:00:00', strtotime('+3 day'));
$days3Fn = date('Y-m-d 23:59:59', strtotime('+3 day'));

$days4St = date('Y-m-d 00:00:00', strtotime('+4 day'));
$days4Fn = date('Y-m-d 23:59:59', strtotime('+4 day'));

$days5St = date('Y-m-d 00:00:00', strtotime('+5 day'));
$days5Fn = date('Y-m-d 23:59:59', strtotime('+5 day'));

$today = date('Y-m-d 00:00:00');

$txt = '';
$errorMessage = '';
$arrayNumBd = [];

$action = $simpla->request->post('action');
$min1 = $simpla->request->post('min1');
$min1IDcomp = $simpla->request->post('min1_comp');
$min2 = $simpla->request->post('min2');
$min2IDcomp = $simpla->request->post('min2_comp');
$min3 = $simpla->request->post('min3');
$min3IDcomp = $simpla->request->post('min3_comp');
$min4 = $simpla->request->post('min4');
$min4IDcomp = $simpla->request->post('min4_comp');
$min5 = $simpla->request->post('min5');
$min5IDcomp = $simpla->request->post('min5_comp');
if (empty($min1) &&  empty($min2) && empty($min3) && empty($min4) && empty($min5) ) {
    echo json_encode([
        'success' => false,
        'error' => 'Вы не выбрали дни!',
    ]);
    return;
}

if (!empty($min1)) {

    $queryNums = $simpla->db->placehold("SELECT user_id FROM __ccprolongations_send_vox WHERE company_id = $min1IDcomp  AND date_send = '$today ' AND type = '1' ");
    $simpla->db->query($queryNums);
    $arrNummsToday = json_decode(json_encode($simpla->db->results('user_id')), true);

    if (!$arrNummsToday) {
        $arrayNumBd[] = '123';
    } else {
        $arrayNumBd = $arrNummsToday;
    }


    $query = $simpla->db->placehold("SELECT user_id FROM __user_balance WHERE payment_date BETWEEN '$days1St' AND '$days1Fn' AND user_id NOT IN (?@) AND last_prolongation != 2 ", $arrayNumBd);
    $simpla->db->query($query);
    $arrMiss = json_decode(json_encode($simpla->db->results("user_id")), true);
    if (!$arrMiss) {
        $txt .= "------ (-1) ------  <br>";
        $txt .= "Отправлено контактов: 0  <br>";
        $txt .= "ID компании: ($min1IDcomp) <br>";
        $txt .= "------------------  <br>";
    } else {
        $count = count($arrMiss);
        $m = '';

        foreach ($arrMiss as $key => $item) {
            if (!next($arrMiss)) {
                $m .= "('" . $today . "','" . $item . "','" . $min1IDcomp . "', '1')";
            } else {
                $m .= "('" . $today . "','" . $item . "','" . $min1IDcomp . "', '1'),";
            }
        }

        $queryInsert = $simpla->db->placehold(" INSERT INTO __ccprolongations_send_vox (`date_send`, `user_id`, `company_id`, `type`) VALUES $m ");
        $simpla->db->query($queryInsert);


        $queryUTC = $simpla->db->placehold("SELECT 
            id as user_id,
            s_users.phone_mobile as phone,
            COALESCE(NULLIF(tz.timezone, ''), '+00:00') AS UTC
            FROM __users 
            LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id
            WHERE s_users.id IN (?@)", (array)$arrMiss);

        $simpla->db->query($queryUTC);

        $arrUTC = json_decode(json_encode($simpla->db->results()), true);

        if (mb_strlen($min1IDcomp) > 3) {
            $queryInsertSET = $simpla->db->placehold("UPDATE __settings SET value = $min1IDcomp WHERE name = 'ccprolongations1'  ");
            $simpla->db->query($queryInsertSET);

            $data = [
                "campaign_id" => $min1IDcomp,
                'rows' => json_encode($arrUTC),
            ];
            $res = send($data, $voxDomain, $voxToken);
            $status = !$res["success"] ? 'не успешно' : 'успешно';

            $txt .= "------ (-1) ------  <br>";
            $txt .= "Отправлено контактов: $count  <br>";
            $txt .= "ID компании: ($min1IDcomp) - $status  <br>";
            $txt .= "------------------  <br>";
        } else {
            $txt .= "------ (-1) ------  <br>";
            $txt .= "<spam class='text-danger'> ID - компании меньше 3 символов!</spam><br>";
            $txt .= "------------------  <br>";
        }


    }

    unset(
        $arrUTC,
        $data,
        $queryUTC,
        $queryInsert,
        $m,
        $query,
        $arrMiss,
        $queryInsertSET,
        $count,
        $arrayNumBd,
        $queryNums,
        $arrNummsToday,
    );

}

if (!empty($min2)) {

    $queryNums = $simpla->db->placehold("SELECT user_id FROM __ccprolongations_send_vox WHERE company_id = $min2IDcomp  AND date_send = '$today ' AND type = '4' ");
    $simpla->db->query($queryNums);
    $arrNummsToday = json_decode(json_encode($simpla->db->results('user_id')), true);

    if (!$arrNummsToday) {
        $arrayNumBd[] = '123';
    } else {
        $arrayNumBd = $arrNummsToday;
    }


    $query = $simpla->db->placehold("SELECT user_id FROM __user_balance WHERE payment_date BETWEEN '$days2St' AND '$days2Fn' AND user_id NOT IN (?@) AND last_prolongation != 2 ", $arrayNumBd);
    $simpla->db->query($query);
    $arrMiss = json_decode(json_encode($simpla->db->results("user_id")), true);
    if (!$arrMiss) {
        $txt .= "------ (-2) ------  <br>";
        $txt .= "Отправлено контактов: 0  <br>";
        $txt .= "ID компании: ($min2IDcomp) <br>";
        $txt .= "------------------  <br>";
    } else {
        $count = count($arrMiss);
        $m = '';

        foreach ($arrMiss as $key => $item) {
            if (!next($arrMiss)) {
                $m .= "('" . $today . "','" . $item . "','" . $min2IDcomp . "', '4')";
            } else {
                $m .= "('" . $today . "','" . $item . "','" . $min2IDcomp . "', '4'),";
            }
        }

        $queryInsert = $simpla->db->placehold(" INSERT INTO __ccprolongations_send_vox (`date_send`, `user_id`, `company_id`, `type`) VALUES $m ");
        $simpla->db->query($queryInsert);


        $queryUTC = $simpla->db->placehold("SELECT 
            id as user_id,
            s_users.phone_mobile as phone,
            COALESCE(NULLIF(tz.timezone, ''), '+00:00') AS UTC
            FROM __users 
            LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id
            WHERE s_users.id IN (?@)", (array)$arrMiss);

        $simpla->db->query($queryUTC);

        $arrUTC = json_decode(json_encode($simpla->db->results()), true);

        if (mb_strlen($min2IDcomp) > 3) {
            $queryInsertSET = $simpla->db->placehold("UPDATE __settings SET value = $min2IDcomp WHERE name = 'ccprolongations2'  ");
            $simpla->db->query($queryInsertSET);

            $data = [
                "campaign_id" => $min2IDcomp,
                'rows' => json_encode($arrUTC),
            ];
            $res = send($data, $voxDomain, $voxToken);
            $status = !$res["success"] ? 'не успешно' : 'успешно';

            $txt .= "------ (-2) ------  <br>";
            $txt .= "Отправлено контактов: $count  <br>";
            $txt .= "ID компании: ($min2IDcomp) - $status  <br>";
            $txt .= "------------------  <br>";
        } else {
            $txt .= "------ (-2) ------  <br>";
            $txt .= "<spam class='text-danger'> ID - компании меньше 3 символов!</spam><br>";
            $txt .= "------------------  <br>";
        }


    }

    unset(
        $arrUTC,
        $data,
        $queryUTC,
        $queryInsert,
        $m,
        $query,
        $arrMiss,
        $queryInsertSET,
        $count,
        $arrayNumBd,
        $queryNums,
        $arrNummsToday,
    );

}

if (!empty($min3)) {


    $queryNums = $simpla->db->placehold("SELECT user_id FROM __ccprolongations_send_vox WHERE company_id = $min3IDcomp  AND date_send = '$today ' AND type = '2' ");
    $simpla->db->query($queryNums);
    $arrNummsToday = json_decode(json_encode($simpla->db->results('user_id')), true);

    if (!$arrNummsToday) {
        $arrayNumBd[] = '123';
    } else {
        $arrayNumBd = $arrNummsToday;
    }


    $query = $simpla->db->placehold("SELECT user_id FROM __user_balance WHERE payment_date BETWEEN '$days3St' AND '$days3Fn' AND user_id NOT IN (?@) AND last_prolongation != 2 ", $arrayNumBd);
    $simpla->db->query($query);
    $arrMiss = json_decode(json_encode($simpla->db->results("user_id")), true);
    if (!$arrMiss) {
        $txt .= "------ (-3) ------  <br>";
        $txt .= "Отправлено контактов: 0  <br>";
        $txt .= "ID компании: ($min3IDcomp) <br>";
        $txt .= "------------------  <br>";
    } else {
        $count = count($arrMiss);
        $m = '';

        foreach ($arrMiss as $item) {
            if (!next($arrMiss)) {
                $m .= "('" . $today . "','" . $item . "','" . $min3IDcomp . "', '2')";
            } else {
                $m .= "('" . $today . "','" . $item . "','" . $min3IDcomp . "', '2'),";
            }
        }

        $queryInsert = $simpla->db->placehold(" INSERT INTO __ccprolongations_send_vox (`date_send`, `user_id`, `company_id`, `type`) VALUES $m ");
        $simpla->db->query($queryInsert);


        $queryUTC = $simpla->db->placehold("SELECT 
            id as user_id,
            s_users.phone_mobile as phone,
            COALESCE(NULLIF(tz.timezone, ''), '+00:00') AS UTC
            FROM __users 
            LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id
            WHERE s_users.id IN (?@)", (array)$arrMiss);

        $simpla->db->query($queryUTC);

        $arrUTC = json_decode(json_encode($simpla->db->results()), true);

        if (mb_strlen($min3IDcomp) > 3) {
            $queryInsertSET = $simpla->db->placehold("UPDATE __settings SET value = $min3IDcomp WHERE name = 'ccprolongations3' ");
            $simpla->db->query($queryInsertSET);

            $data = [
                "campaign_id" => $min3IDcomp,
                'rows' => json_encode($arrUTC),
            ];
            $res = send($data, $voxDomain, $voxToken);
            $status = !$res["success"] ? 'не успешно' : 'успешно';

            $txt .= "------ (-3) ------  <br>";
            $txt .= "Отправлено контактов: $count  <br>";
            $txt .= "ID компании: ($min3IDcomp) - $status  <br>";
            $txt .= "------------------  <br>";
        } else {
            $txt .= "------ (-3) ------  <br>";
            $txt .= "<spam class='text-danger'> ID - компании меньше 3 символов!</spam><br>";
            $txt .= "------------------  <br>";
        }

    }

    unset(
        $arrUTC,
        $data,
        $queryUTC,
        $queryInsert,
        $m,
        $query,
        $arrMiss,
        $queryInsertSET,
        $count,
        $arrayNumBd,
        $queryNums,
        $arrNummsToday,
    );
}

if (!empty($min4)) {


    $queryNums = $simpla->db->placehold("SELECT user_id FROM __ccprolongations_send_vox WHERE company_id = $min4IDcomp  AND date_send = '$today ' AND type = '5' ");
    $simpla->db->query($queryNums);
    $arrNummsToday = json_decode(json_encode($simpla->db->results('user_id')), true);
    if (!$arrNummsToday) {
        $arrayNumBd[] = '123';
    } else {
        $arrayNumBd = $arrNummsToday;
    }


    $query = $simpla->db->placehold("SELECT user_id FROM __user_balance WHERE payment_date BETWEEN '$days4St' AND '$days4Fn' AND user_id NOT IN (?@) AND last_prolongation != 2 ", $arrayNumBd);
    $simpla->db->query($query);
    $arrMiss = json_decode(json_encode($simpla->db->results("user_id")), true);

    if (!$arrMiss) {
        $txt .= "------ (-4) ------  <br>";
        $txt .= "Отправлено контактов: 0  <br>";
        $txt .= "ID компании: ($min4IDcomp) <br>";
        $txt .= "------------------  <br>";
    } else {
        $count = count($arrMiss);
        $m = '';

        foreach ($arrMiss as $item) {
            if (!next($arrMiss)) {
                $m .= "('" . $today . "','" . $item . "','" . $min4IDcomp . "', '5')";
            } else {
                $m .= "('" . $today . "','" . $item . "','" . $min4IDcomp . "', '5'),";
            }
        }

        $queryInsert = $simpla->db->placehold(" INSERT INTO __ccprolongations_send_vox (`date_send`, `user_id`, `company_id`, `type`) VALUES $m ");

        $simpla->db->query($queryInsert);


        $queryUTC = $simpla->db->placehold("SELECT 
            id as user_id,
            s_users.phone_mobile as phone,
            COALESCE(NULLIF(tz.timezone, ''), '+00:00') AS UTC
            FROM __users 
            LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id
            WHERE s_users.id IN (?@)", (array)$arrMiss);

        $simpla->db->query($queryUTC);

        $arrUTC = json_decode(json_encode($simpla->db->results()), true);
        if (mb_strlen($min4IDcomp) > 3) {
            $queryInsertSET = $simpla->db->placehold("UPDATE __settings SET value = $min4IDcomp WHERE name = 'ccprolongations4' ");
            $simpla->db->query($queryInsertSET);

            $data = [
                "campaign_id" => $min4IDcomp,
                'rows' => json_encode($arrUTC),
            ];
            $res = send($data, $voxDomain, $voxToken);
            $status = !$res["success"] ? 'не успешно' : 'успешно';

            $txt .= "------ (-4) ------  <br>";
            $txt .= "Отправлено контактов: $count  <br>";
            $txt .= "ID компании: ($min4IDcomp) - $status  <br>";
            $txt .= "------------------  <br>";
        } else {
            $txt .= "------ (-4) ------  <br>";
            $txt .= "<spam class='text-danger'> ID - компании меньше 3 символов!</spam><br>";
            $txt .= "------------------  <br>";
        }

    }

    unset(
        $arrUTC,
        $data,
        $queryUTC,
        $queryInsert,
        $m,
        $query,
        $arrMiss,
        $queryInsertSET,
        $count,
        $arrayNumBd,
        $queryNums,
        $arrNummsToday,
    );
}

if (!empty($min5)) {
    
    $queryNums = $simpla->db->placehold("SELECT user_id FROM __ccprolongations_send_vox WHERE company_id = $min5IDcomp  AND date_send = '$today ' AND type = '3' ");
    $simpla->db->query($queryNums);
    $arrNummsToday = json_decode(json_encode($simpla->db->results('user_id')), true);

    if (!$arrNummsToday) {
        $arrayNumBd[] = '123';
    } else {
        $arrayNumBd = $arrNummsToday;
    }


    $query = $simpla->db->placehold("SELECT user_id  FROM __user_balance WHERE payment_date BETWEEN '$days5St' AND '$days5Fn' AND user_id NOT IN (?@) ", $arrayNumBd);
    $simpla->db->query($query);
    $arrMiss = json_decode(json_encode($simpla->db->results("user_id")), true);
    if (!$arrMiss) {
        $txt .= "------ (-5) ------  <br>";
        $txt .= "Отправлено контактов: 0  <br>";
        $txt .= "ID компании: ($min5IDcomp) <br>";
        $txt .= "------------------  <br>";
    } else {
        $count = count($arrMiss);
        $m = '';

        foreach ($arrMiss as $item) {
            if (!next($arrMiss)) {
                $m .= "('" . $today . "','" . $item . "','" . $min5IDcomp . "', '3')";
            } else {
                $m .= "('" . $today . "','" . $item . "','" . $min5IDcomp . "', '3'),";
            }
        }

        $queryInsert = $simpla->db->placehold(" INSERT INTO __ccprolongations_send_vox (`date_send`, `user_id`, `company_id`, `type`) VALUES $m ");
        $simpla->db->query($queryInsert);


        $queryUTC = $simpla->db->placehold("SELECT 
            id as user_id,
            s_users.phone_mobile as phone,
            COALESCE(NULLIF(tz.timezone, ''), '+00:00') AS UTC
            FROM __users 
            LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id
            WHERE s_users.id IN (?@)", (array)$arrMiss);

        $simpla->db->query($queryUTC);

        $arrUTC = json_decode(json_encode($simpla->db->results()), true);
        if (mb_strlen($min5IDcomp) > 3) {
            $queryInsertSET = $simpla->db->placehold("UPDATE __settings SET value = $min5IDcomp WHERE name='ccprolongations5'   ");
            $simpla->db->query($queryInsertSET);

            $data = [
                "campaign_id" => $min5IDcomp,
                'rows' => json_encode($arrUTC),
            ];

            $res = send($data, $voxDomain, $voxToken);

            $status = !$res["success"] ? 'не успешно' : 'успешно';


            $txt .= "------ (-5) ------  <br>";
            $txt .= "Отправлено контактов: $count  <br>";
            $txt .= "ID компании: ($min5IDcomp) - $status  <br>";
            $txt .= "------------------  <br>";
        } else {
            $txt .= "------ (-5) ------  <br>";
            $txt .= "<spam class='text-danger'> ID - компании меньше 3 символов!</spam><br>";
            $txt .= "------------------  <br>";
        }

    }

}


echo json_encode([
    'success' => true,
    'text' => $txt,
]);
return;