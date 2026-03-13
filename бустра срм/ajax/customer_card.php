<?php
/**
 * AJAX endpoint для поиска клиента по номеру телефона при входящем звонке Voximplant
 *
 * Параметры:
 * - phone: номер телефона клиента (phone_b) — обязательный
 * - phone_a: номер горячей линии для автоопределения site_id (опционально)
 * - site_id: ID сайта для поиска (опционально, если 'all' или пусто - определяется по phone_a)
 *
 * Логика определения site_id:
 * 1. Если передан phone_a и site_id='all' - определяем site_id по номеру горячей линии
 * 2. Если site_id указан явно (boostra, soyaplace) - используем его
 * 3. Fallback: ищем сначала в boostra, потом в soyaplace
 */

use chats\main\Users;

error_reporting(0);
ini_set('display_errors', 'Off');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();

$phone = $simpla->request->get('phone');
$phoneA = $simpla->request->get('phone_a');
$siteId = $simpla->request->get('site_id');

$response = [];
$detectedSiteId = null;
if (!empty($phoneA) && ($siteId === 'all' || empty($siteId))) {
    $detectedSiteId = $simpla->organizations->getSiteIdByHotlinePhone($phoneA);
    if ($detectedSiteId) {
        $siteId = $detectedSiteId;
        $response['detectedSiteId'] = $detectedSiteId;
    }
}

if (!empty($phone)) {
    $userData = null;
    $searchedSite = null;

    if ($siteId === 'soyaplace') {
        $userData = Users::getUserInfoByPhoneAndSite($phone, 'soyaplace');
        $searchedSite = 'soyaplace';
    } elseif ($siteId === 'boostra') {
        $userData = Users::getUserInfoByPhoneAndSite($phone, 'boostra');
        $searchedSite = 'boostra';
    } else {
        $userData = Users::getUserInfoByPhoneAndSite($phone, 'boostra');
        $searchedSite = 'boostra';

        if (!$userData) {
            $userData = Users::getUserInfoByPhoneAndSite($phone, 'soyaplace');
            $searchedSite = 'soyaplace';
        }
    }

    if ($userData) {
        $response['userId'] = $userData->id;
        $response['siteId'] = $searchedSite;
        $response['clientName'] = trim($userData->lastname . ' ' . $userData->firstname . ' ' . $userData->patronymic);
        $response['rootURL'] = $simpla->config->root_url;

        if (!empty($userData->active_order_id)) {
            $response['activeLoanId'] = $userData->active_order_id;
            $response['loanAmount'] = $userData->loan_amount;
            $response['loanStatus'] = $userData->loan_status;
        }
    }
}

echo json_encode($response);
