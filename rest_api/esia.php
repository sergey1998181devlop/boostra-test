<?php

include_once 'inc/header.php';
include_once 'inc/esia.php';

$simpla = new Simpla();
$action = $simpla->request->get('esia');

$esiaUser = false;
$provider = esiaGetProvider();

if ($action === 'auth') {
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2.esia.state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
} elseif ($action === 'login') {
    $state = $simpla->request->get('state');
    $code = $simpla->request->get('code');
    if ($_SESSION['oauth2.esia.state'] !== $state) {
        header('Location: /401');
        exit();
    }
    $token = $provider->getAccessToken('authorization_code', ['code' => $code]);
    $esiaPersonData = $provider->getResourceOwner($token);
    $esiaUser = $this->Esia->getEsiaPassportData($esiaPersonData->toArray());
    var_dump($esiaUser);
    exit();
    $user = $simpla->users->get_user_esia($esiaUser->resourceOwnerId);
} else {
    header('Location: /404');
}