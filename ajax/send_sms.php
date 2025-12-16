<?php
 
 error_reporting(-1);
 ini_set('display_errors', 'Off');
 session_start();
 
 require_once('../api/Simpla.php');
 require_once('../api/Helpers.php');

 $simpla = new Simpla();

 // Получим телефон
 $phone = $simpla->request->post('phone');
 
 $whatsapp = $simpla->request->post('whatsapp', 'integer');
 
 // Повторная смс?
 $repeat = $simpla->request->post('repeat');
 //print_r($repeat);
 //$repeat = false;

 $flag = $simpla->request->post('flag');
 
 if (!session_id())
     @ session_start();
 
 
 $_SESSION['check_sms_count'] = 0;
 $result = array();
 //if (!isset($_SESSION['sms']) || $repeat) {
 if ((isset($_SESSION['sms_count']) && $_SESSION['sms_count'] < 30) || !isset($_SESSION['sms_count'])) {
 
     /*
     if (!$repeat && !$simpla->recaptcha->check($simpla->request->post('g-recaptcha-response')))
     {
         $result['error'] = 'recaptcha_error';
         header("Content-type: application/json; charset=UTF-8");
         echo json_encode($result);
         exit;
     }
     */
 
     if ($simpla->request->post('check_user')) {
         if (!$simpla->users->get_phone_user($phone)) {
            if (empty($_SERVER['HTTP_REFERER']))
            {
                $result['error'] = 'Обновите страницу и попробуйте еще раз';
                 header("Content-type: application/json; charset=UTF-8");
                 echo json_encode($result);
                 exit;
            }        
            else
            {
                 $soap = $simpla->soap->get_uid_by_phone($phone);
                 if (empty($soap->uid)) {
                     $result['error'] = 'Пользователь не найден';
                     header("Content-type: application/json; charset=UTF-8");
                     echo json_encode($result);
                     exit;
                 }
                
            }
         }
     }
 
     if ($messenger = $simpla->request->post('messenger'))
     {
         $phone = $simpla->users->clear_phone($phone);
         
         switch ($messenger):
             
             case 'whatsapp':
                 $result['link'] = $simpla->config->root_url.'/chats.php?api=whatsapp&method=sendCode&phone='.$phone;
             break;
             
             case 'viber':
                 $result['link'] = 'viber://pa?chatURI='.$simpla->config->viberBotName.'&context=sendCode&text=/start '.$phone;
             break;
             
             case 'telegram':
                 $result['link'] = 'tg://resolve?domain='.$simpla->config->tlgBotName.'&start='.$phone;
             break;
             
             default:
                 $result['error'] = 'UNDEFINED';
         endswitch;
         
         header("Content-type: application/json; charset=UTF-8");
         echo json_encode($result);
         exit;
     }
 
 
     $clear_phone = $simpla->users->clear_phone($phone);
     $_SESSION['phone'] = $clear_phone; // На всякий случай тут запомним
     $code = mt_rand(1000, 9999);

     if ($flag == 'LOGIN') {
         $number_auth_sms = 1;
         $last_sms = $simpla->sms->getLastSmsForAuth($clear_phone, $simpla->sms::TYPE_AUTH);

         if (!empty($last_sms)) {
             $dateCurrent = new DateTime();
             $dateCreatedSms = new DateTime($last_sms->created);
             $dateDiffLimit = new DateTime('-10 minutes');

             // Проверяем, истекли ли 10 минут с момента отправки последней SMS
             $isSmsExpired = ($dateCreatedSms <= $dateDiffLimit);

             // Проверяем, наступил ли новый день
             $isNewDay = ($dateCreatedSms < new DateTime('today midnight'));

             // Получаем общее количество SMS за сегодня
             $totalSmsToday = $simpla->sms->getAuthTotalTodaySms($clear_phone);

             // Если SMS еще не истекла (отправлена менее 10 минут назад), берем её код
             if (!$isSmsExpired) {
                 $code = $last_sms->code;
             }

             // Обновляем номер SMS, если:
             // 1. Наступил новый день И SMS истекла, ИЛИ
             // 2. SMS истекла, но новый день еще не наступил
             if (($isNewDay && !$isSmsExpired) || !$isNewDay) {
                 $number_auth_sms = $totalSmsToday + 1;
             }
         }
     }

     $_SESSION['sms'] = $code;

     setcookie('init_user_phone', $clear_phone, time() + 86400 * 365, '/', $simpla->config->main_domain);
     $_SESSION['init_user_code'] = $code;
 
     $simpla->authcodes->add_authcode(array(
         'phone' => $phone,
         'code' => $code,
         'created' => date('Y-m-d H:i:s')
     ));
     if (isset($_SESSION['sms_count']))
         $_SESSION['sms_count'] ++;
     else
         $_SESSION['sms_count'] = 1;
 
     if ($flag == 'LOGIN') {
         $existUser = $simpla->users->get_user($phone);

         if ($existUser) {
             $msg = "Ваш код №$number_auth_sms для входа в ЛК на {$simpla->config->main_domain}: $code";
         } else {
             $msg = "$code - код в смс №$number_auth_sms, введите его для авторизации на сайте {$simpla->config->main_domain}";
         }
     } else {
         $msg = $_SESSION['sms'];
     }
 
     $_SESSION['sms_date'] = strtotime('now');
 
     if (empty($simpla->is_developer) && empty($simpla->is_admin)) {
         if (empty($whatsapp)) 
         {
             $border_date = date('Y-m-d H:i:s', time() - 86400);
             $sent_sms = $simpla->sms->get_sent_sms($phone, $border_date);
             
             $daily_count = 0;
             $hour_count = 0;
             $last_sent = NULL;
             foreach ($sent_sms as $sms_item)
             {
                 if (strtotime($sms_item->created) > (time() - 3600) && !empty($sms_item->validated)) {
                     $hour_count++;
                 }
                 $daily_count++;

                 if (empty($last_sent) || strtotime($last_sent) > strtotime($sms_item->created)) {
                     $last_sent = $sms_item->created;
                 }
             }
             
             if ($daily_count > 40)
             {
                 $result['time_error'] = 'Вы исчерпали лимит смс-сообщений. Попробуйте повторить через сутки';
             }
             elseif ($hour_count > 6)
             {
                 $result['time_error'] = 'Вы исчерпали лимит смс-сообщений. Попробуйте повторить через час';
             }
             /*
             elseif (strtotime($last_sent) > (time() - 300))
             {
                 $last_time = strtotime($last_sent) + 300 - time();
                 if ($last_time > 60)
                     $result['time_error'] = 'Следуюшее сообщение можно отправить через '.ceil($last_time/60).' мин';
                 else
                     $result['time_error'] = 'Следуюшее сообщение можно отправить через '.$last_time.' сек';                    
                 
             }
            */

             else
             {

                 if ($simpla->request->post('flag') === 'LOGIN') {
                     $sms_validate = Helpers::validateFloodSMS($simpla, 10, $_SESSION['phone']);
                     if (!$sms_validate) {
                         $simpla->response->json(['error' => 'Смс не было отправлено']);
                     }
                 }

                 $sms_validate = $simpla->sms_validate->getRow(null, $_SESSION['phone']);
                 $sms_total = ($sms_validate->total ?? 0) + 1;
                 $isInitUserPage = $simpla->request->post('page') == 'init_user';

                 if (empty($_SESSION['init_captcha_login']) && ((!$simpla->settings->registration_disabled_captcha && $isInitUserPage) || !$isInitUserPage)) {
                     $smart_token = $simpla->request->post('smart-token') ?: '';
                     $result = array_merge($result, \api\helpers\Captcha::validateRequest($sms_total, $smart_token));
                 }

                 if (empty($result['captcha'])) {
                     $convert_msg = iconv('utf8', 'cp1251', $msg);
                     $result['send_id'] = $simpla->notify->send_sms($phone, $convert_msg);
                     $result['daily'] = $daily_count;
                     $result['hour'] = $hour_count;
                     $result['number_sms'] = $number_auth_sms ?? null;

                     $data_sms = [
                         'phone' => $phone,
                         'message' => $msg,
                         'send_id' => $result['send_id'],
                         'created' => date('Y-m-d H:i:s', time()),
                         'code' => $code,
                     ];

                     if ($flag == 'LOGIN') {
                         $data_sms['type'] = $simpla->sms::TYPE_AUTH;
                         if (empty($isSmsExpired)) {
                             $data_sms['is_last_sms'] = 1;
                         }
                     }

                     $simpla->sms->add_message($data_sms);

                     if (!empty($_SESSION['init_captcha_login'])) {
                         unset($_SESSION['init_captcha_login']);
                     }
                 }

                 $insert_data = [
                     'phone' => $_SESSION['phone'],
                     'ip' => $_SERVER['REMOTE_ADDR'],
                     'sms_time' => time(),
                     'total' => ($sms_validate->total ?? 0) + 1
                 ];
                 $simpla->sms_validate->updateFloodSMS($sms_validate, $insert_data);
             }
         } else {
             require_once($simpla->config->root_dir . 'api/addons/ChatMessage.php');
             $chatMessage = new ChatMessage();
 
             $result['whatsapp_sended'] = $chatMessage->messageSend($phone, $msg);
         }
     } else {
         $result['developer_code'] = $_SESSION['sms'];
         $result['block'] = '123';
     }
 }
//}
 
 
 session_write_close();
 
 
 header("Content-type: application/json; charset=UTF-8");
 header("Cache-Control: must-revalidate");
 header("Pragma: no-cache");
 header("Expires: -1");
 print json_encode($result);