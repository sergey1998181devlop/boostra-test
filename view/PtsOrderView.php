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

class PtsOrderView extends View
{
  //////////////////////////////////////////
  // Изменения товаров в корзине
  //////////////////////////////////////////
	public function fetch()
	{  		

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

				$this->db->query('SELECT id FROM __users WHERE phone_mobile=?', $phone);
				$user_id  = $this->db->result('id');

				if(!empty($_COOKIE["utm_source"]))
					$utm_source = $_COOKIE["utm_source"];
				else
					$utm_source = '';

				if(!empty($_COOKIE["utm_medium"]))
					$utm_medium = $_COOKIE["utm_medium"];
				else
					$utm_medium = '';

				if(!empty($_COOKIE["utm_campaign"]))
					$utm_campaign = $_COOKIE["utm_campaign"];
				else
					$utm_campaign = '';

				if(!empty($_COOKIE["utm_content"]))
					$utm_content = $_COOKIE["utm_content"];
				else
					$utm_content = '';

				if(!empty($_COOKIE["utm_term"]))
					$utm_term = $_COOKIE["utm_term"];
				else
					$utm_term = '';

				if(!empty($_COOKIE["webmaster_id"]))
					$webmaster_id = $_COOKIE["webmaster_id"];
				else
					$webmaster_id = '';

				if(!empty($_COOKIE["click_hash"]))
					$click_hash = $_COOKIE["click_hash"];
				else
					$click_hash = '';


				if(!$user_id)
				{	
					$lastname = mb_convert_case($this->request->post('lastname'), MB_CASE_TITLE);
					$firstname = mb_convert_case($this->request->post('firstname'), MB_CASE_TITLE);
					$patronymic = mb_convert_case($this->request->post('patronymic'), MB_CASE_TITLE);
					$birthday = $this->request->post('birthday');
					$email = $this->request->post('email');

					$passportCode = $this->request->post('passportCode');
					$passportDate = $this->request->post('passportDate');
					$subdivisionCode = $this->request->post('subdivisionCode');
					$passportWho = mb_convert_case($this->request->post('passportWho'), MB_CASE_UPPER);

					$Regregion = $this->request->post('Regregion');
					$Regcity = $this->request->post('Regcity');
					$Regstreet = $this->request->post('Regstreet');
					$Reghousing = $this->request->post('Reghousing');
					$Regbuilding = $this->request->post('Regbuilding');
					$Regroom = $this->request->post('Regroom');

					$Faktregion = $this->request->post('Faktregion');
					$Faktcity = $this->request->post('Faktcity');	
					$Faktstreet = $this->request->post('Faktstreet');
					$Fakthousing = $this->request->post('Fakthousing');
					$Faktbuilding = $this->request->post('Faktbuilding');
					$Faktroom = $this->request->post('Faktroom');

					$partner_id = $this->request->post('partner_id');
					$partner_name = $this->request->post('partner_name');

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

					if(!empty($user_id))
						$new_user = true;
					else
						$new_user = false;

				}

			}

			if(!empty($user_id)) 
			{
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
			}

			
			//&& $new_user
			if(!empty($order_id) && !empty($user_id))
			{
				unset($_SESSION['sms']);

				if($utm_source=='leadgid')
					$pixel = '
					<!— Offer Conversion: Boostra —>
					<iframe src="https://go.leadgid.ru/aff_l?offer_id=2745&adv_sub='.$order_id.'" scrolling="no" frameborder="0" width="1" height="1"></iframe>
					<!— // End Offer Conversion —>
					';
				
				if(!empty($pixel))
					$this->design->assign('pixel', $pixel);
				
			}

		}	


		$this->design->assign('amount', $amount);
		$this->design->assign('period', $period);

		//$this->design->assign('error', 'unknown error');

		return $this->design->fetch('ptsorder.tpl');	
	}

	function logit($t) {
		file_put_contents('logs/log-'.date('Y-m-d-H').'.txt', date('H:i:s').': '.$t."\n", FILE_APPEND);
	}
}