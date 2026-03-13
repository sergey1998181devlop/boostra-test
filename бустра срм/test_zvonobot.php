<?php

require_once('api/Smssender.php');
exit;
$client = new Smssender();

//$id = 83459943;

//$result = $client->call('9991701912');
//echo $result['data']['id'];

$result = $client->send_sms_new('79991701912', iconv('utf8', 'cp1251', 'Код: 34543534'));

echo __FILE__.' '.__LINE__.'<br /><pre>';
var_dump($result);
echo '</pre><hr />';
