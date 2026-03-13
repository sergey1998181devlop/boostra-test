<?php
ini_set("soap.wsdl_cache_enabled", 0);

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */
include('addons/sms.php');
include('addons/sms_new.php');
require_once('addons/ChatMessage.php');

class Notify extends Simpla
{
    function email($to, $subject, $message, $from = '', $reply_to = '', $filenames = array())
    {
        $EOL = "\r\n";

        $subject = "=?utf-8?B?".base64_encode($subject)."?=";

        if (empty($filenames))
        {
        	$headers = "MIME-Version: 1.0{$EOL}" ;
    	    $headers .= "Content-type: text/html; charset=utf-8; {$EOL}";
        	$headers .= "From: {$from}{$EOL}";
        	if(!empty($reply_to))
    	    	$headers .= "reply-to: $reply_to{$EOL}";

        	@mail($to, $subject, $message, $headers);
        }
        else
        {
            $boundary = "--".md5(uniqid(time()));
        	$headers = "MIME-Version: 1.0{$EOL}" ;
    	    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"{$EOL}";
        	$headers .= "From: {$from}{$EOL}";
        	if(!empty($reply_to))
    	    	$headers .= "reply-to: $reply_to{$EOL}";

            $multipart = "--{$boundary}{$EOL}";
            $multipart .= "Content-Type: text/html; charset=utf-8{$EOL}";
            $multipart .= "Content-Transfer-Encoding: base64{$EOL}";
            $multipart .= "{$EOL}";
            $multipart .= chunk_split(base64_encode($message));

            foreach ($filenames as $filename)
            {
                $temp_filename = $this->config->root_dir.$this->config->mail_temp_dir.$boundary;

                if (copy($filename, $temp_filename) && ($fp = fopen($temp_filename,"r")))
                {
                    $fileinfo = pathinfo($filename);

                    $multipart .= "{$EOL}--{$boundary}{$EOL}";
                    $multipart .= "Content-Type: application/octet-stream; name=\"{$fileinfo['basename']}\"{$EOL}";
                    $multipart .= "Content-Transfer-Encoding: base64{$EOL}";
                    $multipart .= "Content-Disposition: attachment; filename=\"{$fileinfo['basename']}\"{$EOL}";
                    $multipart .= "{$EOL}";

                    $file64 = fread($fp, filesize($temp_filename));
                    fclose($fp);

                    $multipart .= chunk_split(base64_encode($file64));
                    unlink($temp_filename);
                }
            }
            $multipart .= "{$EOL}--{$boundary}--{$EOL}";

            mail($to, $subject, $multipart, $headers);
        }

    }

    public function send_sms($phone, $text, $from='Boostra.ru', $rus=0)
    {
		if (in_array($phone, array('79051158610', '77787780060'))) {
			return false;
		}
		
        list($sms_id, $sms_cnt) = send_sms_new($phone, $text, $from, 0);

        $statuses = array(
            1 => 'Ошибка в параметрах.',
            2 => 'Неверный логин или пароль. Также возникает при попытке отправки сообщения с IP-адреса, не входящего в список разрешенных Клиентом (если такой список был настроен Клиентом ранее).',
            3 => 'Недостаточно средств на счете Клиента.',
            4 => 'IP-адрес временно заблокирован из-за частых ошибок в запросах. Подробнее',
            5 => 'Неверный формат даты.',
            6 => 'Сообщение запрещено (по тексту или по имени отправителя). Также данная ошибка возникает при попытке отправки массовых и (или) рекламных сообщений без заключенного договора.',
            7 => 'Неверный формат номера телефона.',
            8 => 'Сообщение на указанный номер не может быть доставлено.',
            9 => 'Отправка более одного одинакового запроса на передачу SMS-сообщения либо более пяти одинаковых запросов на получение стоимости сообщения в течение минуты.'
        );

        if($sms_id != 0) {
            return $sms_id;
        } else {
            if (isset($statuses[$sms_cnt])) {
                return $statuses[$sms_cnt];
            }
            return 'Неизвестная ошибка' . $sms_cnt;
        }
    }

    public function send_sms_old($phone, $text, $from='Boostra.ru', $rus=1)
    {
    	list($sms_id, $error)=Sms::send($phone, $text, $from, $rus);

    	//print_r($phone);
    	//print_r($text);
    	//print_r($from);
    	//print_r($rus);

    	if(!$error)
    		return $sms_id;
    	else
    		return $error;
    }

	public function send_chat_message($phone, $text)
    {
    	$chatMessage = new ChatMessage();
		$status = $chatMessage->messageSend($phone, $text);
		return $status;
    }

    public function soap_send_zayavka($z)
    {
    	// Функция отправки заявки в 1с

		// Очистим телефон от лишних символов
		$replace = array('(',')',' ','-');
		$z->phone_mobile = str_replace('+7','8',str_replace($replace,'',$z->phone_mobile));
		$z->passport_serial = str_replace(' ','', $z->passport_serial);
		$z->ТекстЗапроса = json_encode($z, JSON_UNESCAPED_UNICODE);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavki', (array)$z);

		$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
		$returned = $client->__soapCall('GetZayavki',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavki', (array)$z, (array)$returned);
		return $returned;
    }
    
    // перенес в api/Soap.php 30.11.2021 (проверить и удалить)
    public function soap_repeat_zayavka($amount, $period, $user_id, $card)
    {
        if ($user = $this->users->get_user((int)$user_id))
        {
            $z = new StdClass();

            $z->Uid = $user->uid;

      		$z->site_id = $user->site_id;
            $z->partner_id = $user->partner_id;
    		if(empty($user->partner_name))
    			$z->partner_name = 'Boostra';
    		else
    			$z->partner_name = $user->partner_name;

    		// Информация из базы по заявке
    		$z->amount = intval($amount);
    		$z->period = intval($period);
    		$z->utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"];
    		$z->utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"];
    		$z->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"];
    		$z->utm_content = empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"];
    		$z->utm_term = empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"];
    		$z->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"];
    		$z->click_hash = empty($_COOKIE['click_hash']) ? '' : $_COOKIE['click_hash'];

            $z->ServicesSMS = (int)$user->service_sms;
            $z->ServicesInsure = (int)$user->service_insurance;
            $z->ServicesReason = (int)$user->service_reason;

            $z->ОтказНаСайте = 0;
            $z->ПричинаОтказаНаСайте = '';

            $z->CardID = $card;

            $z->ТекстЗапроса = json_encode($z, JSON_UNESCAPED_UNICODE);

   			if(!isset($client)){
    			try {
    				$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebZayavki.1cws?wsdl");
    				$returned = $client->__soapCall('GetZayavkiUid',array($z));

    			} catch (SoapFault $fault) {
    				$returned = $fault;
    			}
            }

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebZayavki.1cws?wsdl GetZayavkiUid', (array)$z, (array)$returned);

    		return $returned;


        }
    }

    public function soap_send_oplata($z)
    {
    	// Функция отправки оплат в 1с
        $z = (object)$z;
        $z->Partner = 'Boostra';
        
		if(!isset($client))
			$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOplata.1cws?wsdl");

		$returned = $client->__soapCall('GetOplata',array($z));

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOplata.1cws?wsdl GetOplata', (array)$z, (array)$returned);

		return $returned;
    }
    // 64720-15064

    public function soap_get_card_list($uid)
    {
    	$z = new stdClass();
    	$z->UID = $uid;
        $z->Partner = 'Boostra';

        if (empty($z->UID) || $z->UID == 'error')
        {

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, 'ERROR UID. NOT SEND', 'cards.php');
            return false;
        }

		/*
        if(!isset($client))
        {
			try {
				$client = new SoapClient($this->config->url_1c" . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
				$returned = $client->__soapCall('GetCardList',array($z));
			} catch (Exception $fault) {
				$returned = $fault;
			}

            $this->logging(__METHOD__, $this->config->url_1c' . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, (array)$returned, 'cards.php');

		}


        if (!empty($returned->return))
    		return json_decode($returned->return);
		*/
		
		try {
			$returned = $this->tinkoff->get_cardlist($uid);
		} catch (Exception $fault) {
			$this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, (array)$fault, 'cards.php');
			return false;
		}

        $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl GetCardList', (array)$z, (array)$returned, 'cards.php');

        if (!isset($returned['error'])) {
			return $returned;
		}
		
	}

    public function soap_add_card($uid)
    {
    	$z = new stdClass();
    	$z->UID = $uid;
    	$z->Пароль = $this->settings->api_password;
        $z->Partner = 'Boostra';
        
        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl AddCard', (array)$z, 'cards.php');

		$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
		$returned = $client->__soapCall('AddCard',array($z));

		$this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl AddCard', (array)$z, (array)$returned, 'cards.php');

		return json_decode($returned->return);
	}

    public function soap_add_customer($uid)
    {
    	$z = new stdClass();
    	$z->UID = $uid;
    	$z->Пароль = $this->settings->api_password;
        $z->Partner = 'Boostra';
        if(!isset($client))
        {
			try {
				$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
				$returned = $client->__soapCall('AddCustomer',array($z));
			} catch (SoapFault $fault) {
				$returned = $fault;
			}

            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl AddCustomer', (array)$z, (array)$returned);
		}

		return $returned;
	}

    public function soap_get_uid_by_phone($phone)
    {
    	$z = new StdClass();
        $z->Телефон = $phone;
    	$z->Пароль = $this->settings->api_password;

  		$replace = array('(',')',' ','-');
		$z->Телефон = str_replace('+7','8',str_replace($replace,'',$z->Телефон));

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl SearchTel', (array)$z);

		$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
		$returned = $client->__soapCall('SearchTel',array($z));

		$this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl SearchTel', (array)$z, (array)$returned);

		return json_decode($returned->return);
    }


    public function soap_get_uid($user, $debug = false)
    {
    	// Функция отправки юзера в 1с
    	// Убогие русские переменные - из 1с, при переносе будут проблемы
    	$z = new stdClass;
    	$z->Фамилия = $user->lastname;
    	$z->Имя = $user->firstname;
    	$z->Отчество = $user->patronymic;

    	// произведем злодеяния для приведения даты рождения к виду
    	// $pieces = explode(" ", $pizza);
    	// [birth] => 31.10.1994 ----> <ДатаРождения>19900122</ДатаРождения>
    	$birth_array = explode(".", $user->birth);
    	$z->ДатаРождения = $birth_array[2].$birth_array[1].$birth_array[0];

    	// Очистим телефон от лишних символов +7 (900) 457-20-72 -----> 79372046907
		$replace = array('+','(',')',' ','-');
		$z->Телефон = str_replace($replace,'',$user->phone_mobile);

    	// Выделить кусочки паспорта
    	// [passport_serial] => 86 14 154274 ----> 8614 и 154274 отдельно
    	$passport_array = explode(" ", $user->passport_serial);
    	$z->ПаспортСерия = $passport_array[0].$passport_array[1];
    	$z->ПаспортНомер = $passport_array[2];

    	// Пароль сайта из опций
    	$z->Пароль = $this->settings->api_password;

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl VozvratUID', (array)$z);

		$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
		$returned = $client->__soapCall('VozvratUID',array($z));

		$this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl VozvratUID', (array)$z, (array)$returned);

		if($debug && ($returned->return == 'Error'))
    		print_r($z);

		return $returned;
    }

    public function notify_new_credit($user, $credit)
    {

    	return true;
    }


    // нерабочая? проверить и удалить
    public function soap_add_files($user_id, $debug = false)
    {
        if ($user = $this->users->get_user((int)$user_id))
        {

            $z = new StdClass();

           	$z->UID = $user->uid;
        	$z->Пароль = $this->settings->api_password;

            $files = $this->users->get_files(array('user_id'=>$user_id));
            $files_dir = $this->config->root_url.'/'.$this->config->users_files_dir;
            $files = array_map(function($var) use ($files_dir){
                $var->url = $files_dir.$var->name;
                return $var;
            }, $files);
            $z->Files = json_encode($files, JSON_UNESCAPED_UNICODE);
//ini_set("soap.wsdl_cache_enabled", 0);
			if(!isset($client)){
    			try {
    				$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
    				$returned = $client->__soapCall('RequestFiles',array($z));
    			} catch (SoapFault $fault) {
    				$returned = $fault;
    			}

                $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl RequestFiles', (array)$z, (array)$returned);
    		}

    		if($debug)
            {
        		echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($z, $returned );echo '</pre><hr />';
            }
    		return $returned;
        }
    }


    public function soap_send_files($user_id, $debug = false, $visible = true)
    {
        if ($user = $this->users->get_user((int)$user_id))
        {

            $z = new StdClass();

           	$z->UID = $user->uid;
        	$z->Пароль = $this->settings->api_password;

            $files = $this->users->get_files(array('user_id'=>$user_id, 'status'=>0), $visible);
            $files_dir = $this->config->root_url.'/'.$this->config->original_images_dir;
            foreach ($files as $kk => $file)
            {
                if (file_exists($this->config->root_dir.$this->config->original_images_dir.$file->name))
                {
                    $file->url = $files_dir.$file->name;
                    if ($file->type == 'passport1' || $file->type == 'passport2')
                        $file->type == 'passport';
                    $file->url = str_replace('https://', 'http://', $file->url);

					/*загружаем в хрвнилище
					$storage_uid = $this->filestorage->upload_file($file->url);
					if ($storage_uid) {
						$file->storage_uid = $storage_uid;
						//запоминаем юид
						$this->users->update_file($file->id, ['storage_uid' => $storage_uid]);
					}
                    */					
                }
                else
                {
                    $this->users->update_file($file->id, array('status'=>3));
                    unset($files[$kk]);
                }
            }

            if (empty($files))
                return false;

            $z->Files = json_encode($files, JSON_UNESCAPED_UNICODE);

            $method = __METHOD__;
            $soapUrl = $this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl";
            $soapCall = 'RequestFiles';

            try {
                $this->setLoggerState($method, $soapUrl . ' ' . $soapCall, (array)$z);
                $client = new SoapClient($soapUrl);
                $returned = $client->__soapCall($soapCall, array($z));
                $this->logging($method, $soapUrl . ' ' . $soapCall, (array)$z, (array)$returned);
            } catch (Exception $e) {
                $this->queue->add($method, $soapUrl, $soapCall, $z);
                $returned = new stdClass;
                $returned->return = 'OK';
            }

    		if($debug)
            {
        		echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($z, $returned );echo '</pre><hr />';
            }
    		return $returned;
        }
    }


    public function soap_mining_files($user_id, $debug = false)
    {
        if ($user = $this->users->get_user((int)$user_id))
        {

            $z = new StdClass();

           	$z->UID = $user->uid;
        	$z->Пароль = $this->settings->api_password;

			if(!isset($client)){
    			try {
    				$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
    				$returned = $client->__soapCall('MiningFiles',array($z));

                    $files = json_decode($returned->return);
                    if (!empty($files))
                    {
                        foreach ($files as $file)
                        {
                            if (!empty($file->ФайлПринят))
                            {
                                $this->users->update_file($file->id, array('status'=>2));
                            }
                            elseif (!empty($file->ФайлОтклонен))
                            {
                                $this->users->update_file($file->id, array('status'=>3));
                            }
                        }
                    }

    			} catch (SoapFault $fault) {
    				$returned = $fault;
    			}

//                $this->logging(__METHOD__, $this->config->url_1c' . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl MiningFiles', (array)$z, (array)$returned);
    		}

    		if($debug)
            {
        		echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($z, $returned );echo '</pre><hr />';
            }
    		return json_decode($returned->return);
        }
    }

    /**
     *  $files = array('file_id' => $new_file_link);
    */
    public function soap_delete_file($file_id)
    {
        $file = $this->users->get_file((int)$file_id);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($file);echo '</pre><hr />';
        if (!empty($file) && $file->status > 0)
        {
            $user = $this->users->get_user((int)$file->user_id);

            $z = new StdClass();

           	$z->UID = $user->uid;
        	$z->Пароль = $this->settings->api_password;

            $send_file = (object)array(
                'id_old' => $file->id,
                'user_id' => $file->user_id,
                'name' => '',
                'type' => $file->type,
                'status' => $file->status,
                'url' => '',
            );
            $z->Files = json_encode(array($send_file), JSON_UNESCAPED_UNICODE);

			$this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ReplacementFiles', (array)$z);

			$client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/Tinkoff.1cws?wsdl");
			$returned = $client->__soapCall('ReplacementFiles',array($z));

			$this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/Tinkoff.1cws?wsdl ReplacementFiles', (array)$z, (array)$returned);

    		return $returned;
        }
    }

	public function email_order_user($order_id)
	{
			if(!($order = $this->orders->get_order(intval($order_id))) || empty($order->email))
				return false;

			$purchases = $this->orders->get_purchases(array('order_id'=>$order->id));
			$this->design->assign('purchases', $purchases);

			$products_ids = array();
			$variants_ids = array();
			foreach($purchases as $purchase)
			{
				$products_ids[] = $purchase->product_id;
				$variants_ids[] = $purchase->variant_id;
			}

			$products = array();
			foreach($this->products->get_products(array('id'=>$products_ids)) as $p)
				$products[$p->id] = $p;

			$images = $this->products->get_images(array('product_id'=>$products_ids));
			foreach($images as $image)
				$products[$image->product_id]->images[] = $image;

			$variants = array();
			foreach($this->variants->get_variants(array('id'=>$variants_ids)) as $v)
			{
				$variants[$v->id] = $v;
				$products[$v->product_id]->variants[] = $v;
			}

			foreach($purchases as &$purchase)
			{
				if(!empty($products[$purchase->product_id]))
					$purchase->product = $products[$purchase->product_id];
				if(!empty($variants[$purchase->variant_id]))
					$purchase->variant = $variants[$purchase->variant_id];
			}

			// Способ доставки
			$delivery = $this->delivery->get_delivery($order->delivery_id);
			$this->design->assign('delivery', $delivery);

			$this->design->assign('order', $order);
			$this->design->assign('purchases', $purchases);

			// Отправляем письмо
			// Если в шаблон не передавалась валюта, передадим
			if ($this->design->smarty->getTemplateVars('currency') === null)
			{
				$this->design->assign('currency', current($this->money->get_currencies(array('enabled'=>1))));
			}
			$email_template = $this->design->fetch($this->config->root_dir.'design/'.$this->settings->theme.'/html/email_order.tpl');
			$subject = $this->design->get_var('subject');
			$this->email($order->email, $subject, $email_template, $this->settings->notify_from_email);

	}


	public function email_order_admin($order_id)
	{
			if(!($order = $this->orders->get_order(intval($order_id))))
				return false;

			$purchases = $this->orders->get_purchases(array('order_id'=>$order->id));
			$this->design->assign('purchases', $purchases);

			$products_ids = array();
			$variants_ids = array();
			foreach($purchases as $purchase)
			{
				$products_ids[] = $purchase->product_id;
				$variants_ids[] = $purchase->variant_id;
			}

			$products = array();
			foreach($this->products->get_products(array('id'=>$products_ids)) as $p)
				$products[$p->id] = $p;

			$images = $this->products->get_images(array('product_id'=>$products_ids));
			foreach($images as $image)
				$products[$image->product_id]->images[] = $image;

			$variants = array();
			foreach($this->variants->get_variants(array('id'=>$variants_ids)) as $v)
			{
				$variants[$v->id] = $v;
				$products[$v->product_id]->variants[] = $v;
			}

			foreach($purchases as &$purchase)
			{
				if(!empty($products[$purchase->product_id]))
					$purchase->product = $products[$purchase->product_id];
				if(!empty($variants[$purchase->variant_id]))
					$purchase->variant = $variants[$purchase->variant_id];
			}

			// Способ доставки
			$delivery = $this->delivery->get_delivery($order->delivery_id);
			$this->design->assign('delivery', $delivery);

			// Пользователь
			$user = $this->users->get_user(intval($order->user_id));
			$this->design->assign('user', $user);

			$this->design->assign('order', $order);
			$this->design->assign('purchases', $purchases);

			// В основной валюте
			$this->design->assign('main_currency', $this->money->get_currency());

			// Отправляем письмо
			$email_template = $this->design->fetch($this->config->root_dir.'simpla/design/html/email_order_admin.tpl');
			$subject = $this->design->get_var('subject');
			$this->email($this->settings->order_email, $subject, $email_template, $this->settings->notify_from_email);

	}



	public function email_comment_admin($comment_id)
	{
			if(!($comment = $this->comments->get_comment(intval($comment_id))))
				return false;

			if($comment->type == 'product')
				$comment->product = $this->products->get_product(intval($comment->object_id));
			if($comment->type == 'blog')
				$comment->post = $this->blog->get_post(intval($comment->object_id));

			$this->design->assign('comment', $comment);

			// Отправляем письмо
			$email_template = $this->design->fetch($this->config->root_dir.'simpla/design/html/email_comment_admin.tpl');
			$subject = $this->design->get_var('subject');
			$this->email($this->settings->comment_email, $subject, $email_template, $this->settings->notify_from_email);
	}

	public function email_password_remind($user_id, $code)
	{
			if(!($user = $this->users->get_user(intval($user_id))))
				return false;

			$this->design->assign('user', $user);
			$this->design->assign('code', $code);

			// Отправляем письмо
			$email_template = $this->design->fetch($this->config->root_dir.'design/'.$this->settings->theme.'/html/email_password_remind.tpl');
			$subject = $this->design->get_var('subject');
			$this->email($user->email, $subject, $email_template, $this->settings->notify_from_email);

			$this->design->smarty->clearAssign('user');
			$this->design->smarty->clearAssign('code');
	}

	public function email_feedback_admin($feedback_id)
	{
			if(!($feedback = $this->feedbacks->get_feedback(intval($feedback_id))))
				return false;

			$this->design->assign('feedback', $feedback);

			// Отправляем письмо
			$email_template = $this->design->fetch($this->config->root_dir.'simpla/design/html/email_feedback_admin.tpl');
			$subject = $this->design->get_var('subject');
			$this->email($this->settings->comment_email, $subject, $email_template, "$feedback->name <$feedback->email>", "$feedback->name <$feedback->email>");
	}

    /**
     * Отправляет смс с паролем от ЛК
     * @param $phone
     * @param $password
     * @return void
     */
    public function sendNewPasswordSms($phone, $password)
    {
        $message = "Ваш новый пароль от личного кабинета Boostra.ru : $password";
        $convert_msg = iconv('utf-8', 'cp1251', $message);

        $result['send_id'] = $this->notify->send_sms($phone, $convert_msg);
        $this->sms->add_message(
            [
                'phone' => $phone,
                'message' => $message,
                'send_id' => $result['send_id'],
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->sms::SMS_PASSWORD_ADD,
            ]
        );

    }
}