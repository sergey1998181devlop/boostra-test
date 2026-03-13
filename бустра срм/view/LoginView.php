<?PHP

require_once('View.php');

class LoginView extends View
{
    
	function fetch()
	{
		// Выход
		if($this->request->get('action') == 'logout')
		{
			unset($_SESSION['manager_id']);
			unset($_SESSION['manager_ip']);
            setcookie('mid', null, time() -1, '/', 'boostra.ru');
            setcookie('ah', null, time() -1, '/', 'boostra.ru');
			header('Location: '.$this->config->root_url);
			exit();
		}
		// Вход
		elseif($this->request->method('post') && $this->request->post('login'))
		{
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($_POST);echo '</pre><hr />';
			$login			= $this->request->post('login');
			$password		= $this->request->post('password');
			
			$this->design->assign('login', $login);
		
			if($manager_id = $this->managers->check_password($login, $password))
			{
                $manager = $this->managers->get_manager($manager_id);
                if (empty($manager->blocked))
                {
                    $update = array();

                    $salt = md5(mt_rand().microtime());
                    $hash = md5(sha1($_SERVER['REMOTE_ADDR'].$manager_id).$salt);

                    setcookie('mid', $manager_id, time() + 7*86400, '/', 'boostra.ru');
                    setcookie('ah', $hash, time() + 7*86400, '/', 'boostra.ru');

                    $update['salt'] = $salt;
                    $update['last_ip'] = $_SERVER['REMOTE_ADDR'];
                    
                    $this->managers->update_manager($manager_id, $update);
    				$_SESSION['manager_id'] = $manager->id;
    				$_SESSION['manager_ip'] = $_SERVER['REMOTE_ADDR'];
                    $this->managers->add((int)$manager->id);
    				if ($back = $this->request->get('back'))
                        header('Location: '.$this->config->root_url.$back);				
                    else
                        header('Location: '. $this->managers->get_after_login_page($manager->role));
    			    exit;
			    }
                else
                {
                    $this->design->assign('error', 'blocked');
                } 
            }
			else
			{
				$this->design->assign('error', 'login_incorrect');
			}				
		}	
		return $this->design->fetch('login.tpl');
	}	
}
