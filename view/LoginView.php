<?PHP

require_once('View.php');

class LoginView extends View
{
	function fetch()
	{
		// Выход
		if($this->request->get('action') == 'logout')
		{
			unset($_SESSION['user_id']);
			unset($_SESSION['looker_mode']);
            unset($_SESSION['passport_user']);
            unset($_SESSION[$this->account_contract::SESSION_KEY]);
            unset($_SESSION['hide_asp_modal']);
            unset($_SESSION['user_info']);
            unset($_SESSION['state']);

            if (($_SESSION['restricted_mode'] ?? null) == 1){
                unset($_SESSION['restricted_mode']);
                unset($_SESSION['restricted_mode_logout_hint']);
                header('Location: '.$this->config->root_url . '/user/login');
                exit();
            }

            setcookie('auth_jwt_token', null, time()-1, '/');

            header('Location: '.$this->config->root_url);
			exit();
		}
		
		// 
		elseif($this->request->method('post') && $this->request->post('login'))
		{
			$key	= $this->request->post('key');
			$phone	= $this->request->post('real_phone');
			
			$this->design->assign('phone', $phone);
            
            if ($key == $_SESSION['sms'])
            {
                $user = $this->users->get_user($phone);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user, $phone);echo '</pre><hr />';            
                if (empty($user))
                {
                    $soap = $this->soap->get_uid_by_phone($phone);
                    if (!empty($soap->result) && !empty($soap->uid))
                    {
                        $expl = explode(' ', $soap->client);
                        $lastname = isset($expl[0]) ? mb_convert_case($expl[0], MB_CASE_TITLE) : '';
                        $firstname = isset($expl[1]) ? mb_convert_case($expl[1], MB_CASE_TITLE) : '';
                        $patronymic = isset($expl[2]) ? mb_convert_case($expl[2], MB_CASE_TITLE) : '';

                        $user_id = $this->users->add_user(array(
                            'UID' => $soap->uid,
                            'UID_status' => "ok",
                            'phone_mobile' => $phone,
                            'lastname' => $lastname,
                            'firstname' => $firstname,
                            'patronymic' => $patronymic,
                            'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
            				'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                            'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                            'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                            'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                            'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                            'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],
                            'enabled' => 1,
                            'sms' => $_SESSION['sms'],
                            'last_ip'=>$_SERVER['REMOTE_ADDR'],
                            'service_sms' => 1,
                            'service_insurance' => 1,
                        ));
                        
                        $_SESSION['user_id'] = $user_id;
                        setcookie('user_id', $user_id, time() + 86400 * 365, '/');
                        header('Location: '.$this->config->root_url.'/user');
                        exit;
                    }
                }
                elseif (empty($user->enabled)) // пользователь неактивен
                {
   					$this->design->assign('error', 'user_disabled');                    
                }
                elseif (empty($user->UID) || trim($user->UID) == 'Error')
                {

                    $soap = $this->soap->get_uid_by_phone($phone);
                    if (!empty($soap->uid))
                    {
                        $this->users->update_user($user->id, array('UID'=>$soap->uid, 'UID_status' => "ok"));
                        
                        $_SESSION['user_id'] = $user->id;
                        setcookie('user_id', $user_id, time() + 86400 * 365, '/');
                        
                        $update = array('last_ip'=>$_SERVER['REMOTE_ADDR']);
        				    
                        $this->users->update_user($user->id, $update);
    
                        header('Location: '.$this->config->root_url.'/user');
                        exit;
                    }
                    elseif ($soap->error == 'Множество совпадений')
                    {
                        $this->soap->send_doubling_phone($phone);
                        
                        $this->design->assign('error', 'user_blocked');
                    }
   					
                }
                else
                {
    					$_SESSION['user_id'] = $user->id;
                        setcookie('user_id', $user_id, time() + 86400 * 365, '/');
    					$this->users->update_user($user->id, array('last_ip'=>$_SERVER['REMOTE_ADDR'], 'fake_order_error' => 0));
    					
    					header('Location: '.$this->config->root_url.'/user');
    					// Проверим заявки пользователя, статусы, для всех не удаленных
    					//$orders = $this->orders->get_orders(array('user_id'=>$user->id, 'status' => array('0','1', '2','3')));
    					//foreach ($orders as $order) {
    						//if(!empty($order->1c_id))
    							//$this->orders->check_order_1c($order->1c_id, $order->id);
    					//}
    					exit();			
                }
            }
            else
            {
                $this->design->assign('error', 'login_incorrect');
            }
			
		}

		if(!empty($this->user))
		{
			header('Location: '.$this->config->root_url.'/user');
			exit();
		}	

		return $this->design->fetch('login.tpl');
	}	
}
