<?php

require_once dirname(__FILE__).'/../api/Telegram.php';
require_once dirname(__FILE__).'/../api/addons/sms_new.php';
require_once dirname(__FILE__).'/../api/Simpla.php';

// TODO: вынести отсюда
$simpla = new Simpla();

$tg = new Telegram(Telegram::BOOSTRA_SMSC_BALANCE['token'], Telegram::BOOSTRA_SMSC_BALANCE['chat_id']);
$balance = get_balance($simpla, 'boostra'); // TODO: Цикл для всех организация, для запроса баланса
$response = $tg->sendMessage('Current SMSC balance: ' . $balance . ' руб.');

exit('TG message send...');
