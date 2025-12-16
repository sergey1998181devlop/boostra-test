<?PHP

/**
 * Simpla CMS
 *
 * @copyright 	2009 Denis Pikusov
 * @link 		http://simp.la
 * @author 		Denis Pikusov
 *
 * Корзина покупок
 * Этот класс использует шаблон cart.tpl
 *
 */

require_once('View.php');

class NewOrderView extends View
{
  //////////////////////////////////////////
  // Изменения товаров в корзине
  //////////////////////////////////////////
	public function fetch()
	{  		
        
        if (!empty($this->user->id) || $this->show_unaccepted_agreement_modal())
        {
            header('Location: '.$this->config->root_url.'/user');
            exit;
        }
        
		if(!$this->request->method('post'))
		{
			$amount = $this->request->get('amount');
			$period = $this->request->get('period');

			if(empty($amount))
				$amount = 5000;

			if(empty($period))
				$period = 10;

			$_SESSION['amount'] = $amount;
			$_SESSION['period'] = $period;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($_SESSION);echo '</pre><hr />';
		}
		else
		{
			// Отправили заявку
			//$name					= $this->request->post('name');
			//$email				= $this->request->post('email');
			//$password				= $this->request->post('password');

			$default_status = 1; // Активен ли пользователь сразу после регистрации (0 или 1)

			//$sheet = $this->request->post('sheet');

			$phone = $this->request->post('phone');
			$check_sms = $this->request->post('check_sms');
			if($_SESSION['sms'] == $check_sms)
			{
				$utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"];
				$utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"];
                $utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"];
                $utm_content = empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"];
                $utm_term = empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"];
                $webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"];
                $click_hash = empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"];

				$lastname = mb_convert_case($this->request->post('lastname'), MB_CASE_TITLE);
				$firstname = mb_convert_case($this->request->post('firstname'), MB_CASE_TITLE);
				$patronymic = mb_convert_case($this->request->post('patronymic'), MB_CASE_TITLE);
				$birthday = (string)$this->request->post('birthday');
				$email = (string)$this->request->post('email');

				$passportCode = (string)$this->request->post('passportCode');
				$passportDate = (string)$this->request->post('passportDate');
                $passportDate = $this->users->tryFormatDate($passportDate);
				$subdivisionCode = (string)$this->request->post('subdivisionCode');
				$passportWho = mb_convert_case($this->request->post('passportWho'), MB_CASE_UPPER);

				$Regregion = (string)$this->request->post('Regregion');
				$Regcity = (string)$this->request->post('Regcity');
				$Regstreet = (string)$this->request->post('Regstreet');
				$Reghousing = (string)$this->request->post('Reghousing');
				$Regbuilding = (string)$this->request->post('Regbuilding');
				$Regroom = (string)$this->request->post('Regroom');

				$Faktregion = $this->request->post('Faktregion') ? (string)$this->request->post('Faktregion') : $Regregion;
				$Faktcity = $this->request->post('Faktcity') ? (string)$this->request->post('Faktcity') : $Regcity;	
				$Faktstreet = (string)$this->request->post('Faktstreet');
				$Fakthousing = (string)$this->request->post('Fakthousing');
				$Faktbuilding = (string)$this->request->post('Faktbuilding');
				$Faktroom = (string)$this->request->post('Faktroom');

				$partner_id = $this->request->post('partner_id') ? $this->request->post('partner_id') : '';
				$partner_name = $this->request->post('partner_name') ? (string)$this->request->post('partner_name') : 'Boostra';

				$check_empty = $this->request->post('check_human');


				if($equal)
				{
					$Faktregion = $Regregion;
					$Faktcity = $Regcity;
					$Faktstreet = $Regstreet;
					$Fakthousing = $Reghousing;
					$Faktbuilding = $Regbuilding;
					$Faktroom = $Regroom;
				}
                
                $zayavochka = new stdClass();

        		$zayavochka->lastname = $lastname;
        		$zayavochka->firstname = $firstname;
        		$zayavochka->patronymic = $patronymic;
        		$zayavochka->birth = !empty($birthday) ? $birthday : '01.01.1900';
        		$zayavochka->phone_mobile = $phone;
        		$zayavochka->email = $email;
        		$zayavochka->passport_serial = $passportCode;
        		$zayavochka->passport_date = $passportDate;
        		$zayavochka->subdivision_code = $subdivisionCode;
        		$zayavochka->passport_issued = $passportWho;
           		$zayavochka->Regregion = $Regregion;
        		$zayavochka->Regdistrict = '';
        		$zayavochka->Regcity = $Regcity;
                $zayavochka->Reglocality = '';
        		$zayavochka->Regstreet = $Regstreet;
        		$zayavochka->Regbuilding = $Regbuilding;
        		$zayavochka->Reghousing = $Reghousing;
        		$zayavochka->Regroom = $Regroom;
        		$zayavochka->Faktregion = $Faktregion;
        		$zayavochka->Faktdistrict = '';
        		$zayavochka->Faktcity = $Faktcity;
        		$zayavochka->Faktlocality = '';
        		$zayavochka->Faktstreet = $Faktstreet;
        		$zayavochka->Faktbuilding = $Faktbuilding;
        		$zayavochka->Fakthousing = $Fakthousing;
                $zayavochka->Faktroom = $Faktroom;
        		$zayavochka->site_id = 'Boostra';
        		$zayavochka->partner_id = $partner_id;
                $zayavochka->partner_name = (empty($partner_name)) ? 'Boostra' : $partner_name;
	
        		$zayavochka->amount = $_SESSION['amount'];
                $zayavochka->period = $_SESSION['period'];
        		$zayavochka->utm_source = $utm_source;
        		$zayavochka->utm_medium = $utm_medium;
        		$zayavochka->utm_campaign = $utm_campaign;
        		$zayavochka->utm_content = $utm_content;
        		$zayavochka->utm_term = $utm_term;
        		$zayavochka->webmaster_id = $webmaster_id;
        		$zayavochka->click_hash = $click_hash;
                $zayavochka->id = '';

                $zayavochka->car = '';
                $zayavochka->IntervalNumber = '';
                $zayavochka->СтатусCRM = '';
                $zayavochka->СуммаCRM = (string)$recomendation_amount;
                $zayavochka->УИД_CRM = exec($this->config->root_dir.'generic/uidgen');
                
                $soap_zayavka = $this->notify->soap_send_zayavka($zayavochka);
sleep(1); // ?? задержка между запросами
                $soap_uid = $this->notify->soap_get_uid_by_phone($phone);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($soap_zayavka, $soap_uid);echo '</pre><hr />';                
                // если есть юайди пользователя, то сохранение заявки прошло успешно
                if (!empty($soap_uid->uid))
                {
                    $user = array(
						'UID' => $soap_uid->uid,
                        'UID_status' => 'ok',
                        'phone_mobile' => $phone,
                        'lastname' => $lastname,
						'firstname' => $firstname,
						'patronymic' => $patronymic,
						'email' => $email,
						'reg_ip' => $_SERVER['REMOTE_ADDR'],
						'site_id' => 'Boostra',
						'partner_id' => $partner_id,
						'partner_name' => $partner_name,    
						'utm_source' => $utm_source,
						'utm_medium' => $utm_medium,
						'utm_campaign' => $utm_campaign,
						'utm_content' => $utm_content,
						'utm_term' => $utm_term,
						'webmaster_id' => $webmaster_id,
						'click_hash' => $click_hash,
						'enabled' => $default_status,
						'sms' => $check_sms
                    );
                    $user_id = $this->users->add_user($user);
                    
                    
    				$order = array(
                        'user_id' => $user_id,
    					'amount' => $_SESSION['amount'],
    					'period' => $_SESSION['period'],
    					'ip' => $_SERVER['REMOTE_ADDR'],
    					'sms' => $check_sms,
    					'utm_source' => $utm_source,
    					'utm_medium' => $utm_medium,
    					'utm_campaign' => $utm_campaign,
    					'utm_content' => $utm_content,
    					'utm_term' => $utm_term,
    					'webmaster_id' => $webmaster_id,
    					'click_hash' => $click_hash
   					);
                    
                    if (empty($soap_zayavka->return->id_zayavka))
                    {
                        $order['status'] = 3;
                        $order['note'] = strval($soap_zayavka->return->Error);
                    }
                    else
                    {
                        $order['status'] = 1;
                        $order['1c_id'] = $soap_zayavka->return->id_zayavka;
                    }
                    
                    $order_id = $this->orders->add_order($order);
                    $this->order_data->set($order_id, $this->order_data::USER_AMOUNT, $order['amount']);
                    
                }
                // если нет юайди пользователя - сохраняем в базе полные 
                // данные о пользователе и заявке для отправки кроном
                else
                {
                    $user =	array(
						'lastname' => $lastname,
						'firstname' => $firstname,
						'patronymic' => $patronymic,
						'birth' => $birthday,
						'phone_mobile' => $phone,
						'email' => $email,
						'reg_ip' => $_SERVER['REMOTE_ADDR'],

						'passport_serial' => $passportCode,
						'passport_date' => $passportDate,
						'subdivision_code' => $subdivisionCode,
						'passport_issued' => $passportWho,

						'Regregion' => $Regregion,
						'Regcity' => $Regcity,
						'Regstreet' => $Regstreet,
						'Reghousing' => $Reghousing,
						'Regbuilding' => $Regbuilding,
						'Regroom' => $Regroom,

						'Faktregion' => $Faktregion,
						'Faktcity' => $Faktcity,	
						'Faktstreet' => $Faktstreet,
						'Fakthousing' => $Fakthousing,
						'Faktbuilding' => $Faktbuilding,
						'Faktroom' => $Faktroom,

						'site_id' => 'Boostra',
						'partner_id' => $partner_id,
						'partner_name' => $partner_name,

						'utm_source' => $utm_source,
						'utm_medium' => $utm_medium,
						'utm_campaign' => $utm_campaign,
						'utm_content' => $utm_content,
						'utm_term' => $utm_term,
						'webmaster_id' => $webmaster_id,
						'click_hash' => $click_hash,

						'enabled' => $default_status,

						'sms' => $check_sms
                    );
                    $user_id = $this->users->add_user($user);
                    
    				$order = array(
    					'user_id' => $user_id,
    					'amount' => $_SESSION['amount'],
    					'period' => $_SESSION['period'],
    					'ip' => $_SERVER['REMOTE_ADDR'],
    					'sms' => $check_sms,
    					'utm_source' => $utm_source,
    					'utm_medium' => $utm_medium,
    					'utm_campaign' => $utm_campaign,
    					'utm_content' => $utm_content,
    					'utm_term' => $utm_term,
    					'webmaster_id' => $webmaster_id,
    					'click_hash' => $click_hash
   					);
    
    				$order_id = $this->orders->add_order($order);
                    $this->order_data->set($order_id, $this->order_data::USER_AMOUNT, $order['amount']);
                }

                // авторизуемся
                if (!empty($user_id))
                {
                    $_SESSION['user_id'] = $user_id;
                    setcookie('user_id', $user_id, time() + 86400 * 365, '/');
                }

    			if(!empty($order_id) && !empty($user_id))
    			{
    				unset($_SESSION['sms']);
    
    				if($utm_source=='leadgid')
                    {
    					$pixel = '
    					<!— Offer Conversion: Boostra —>
    					<iframe src="http://go.leadgid.ru/aff_lsr?offer_id=4806&adv_sub='.$order_id.'&transaction_id='.$click_hash.'" scrolling="no" frameborder="0" width="1" height="1"></iframe>
    					<!— // End Offer Conversion —>
    					';
    				}
    				if(!empty($pixel))
    					$this->design->assign('pixel', $pixel);
    				
    			}

                if(!empty($order_id))
                {
                    $_SESSION['order_id'] = $order_id;
                    
                    setcookie("utm_source", null, time() - 1);
        			setcookie("utm_medium", null, time() - 1);
        			setcookie("utm_campaign", null, time() - 1);
        			setcookie("utm_content", null, time() - 1);
        			setcookie("utm_term", null, time() - 1);
        			setcookie("webmaster_id", null, time() - 1);
        			setcookie("click_hash", null, time() - 1);
        
                    setcookie("checked", null, time() - 1);

                }
                
       
			}
        }

		$this->design->assign('amount', $amount);
		$this->design->assign('period', $period);

		//$this->design->assign('error', 'unknown error');

		return $this->design->fetch('neworder.tpl');	
	}

	function logit($t) {
		file_put_contents('logs/log-'.date('Y-m-d-H').'.txt', date('H:i:s').': '.$t."\n", FILE_APPEND);
	}
}