<?php
error_reporting(0);
ini_set('display_errors', 'off');

header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();

$phone = $simpla->request->get('phone');
$user_id = $simpla->request->get('user_id');
$zaim = $simpla->request->get('zaim');

if (str_contains($user_id, '-')){

    $user_id = $simpla->users->get_uid_user_id($user_id);
}
$code = $simpla->orders->getShortLink($user_id, $zaim);
if (empty($code)) {
    $code = Helpers::generateLink();
    $count = $simpla->orders->getLinkExists($code);
    while ($count > 0) {

        $code = Helpers::generateLink();
        $count = $simpla->orders->getLinkExists($code);
    }
    $simpla->orders->add_short_link([
        'link' => $code,
        'user_id' => $user_id,
        'phone' => $phone,
        'zaim_number' => $zaim,
        'active' => true
    ]);
}

$template = 'Погасите займ через ссылку '. $simpla->config->front_url.'/pay/' . $code;


$simpla->comments->add_comment([
    'manager_id' => 50,
    'user_id' => $user_id,
    'block' => 'prolongation',
    'text' => $template,
    'created' => date('Y-m-d H:i:s'),
]);

$simpla->response->json_output(['link' => $simpla->config->front_url.'/pay/' . $code]);