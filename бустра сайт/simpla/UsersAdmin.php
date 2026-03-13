<?PHP
require_once('api/Simpla.php');

############################################
# Class Properties displays a list of product parameters
############################################
class UsersAdmin extends Simpla
{
	function fetch()
	{	

		if($this->request->method('post'))
		{
			// Действия с выбранными
			$ids = $this->request->post('check');
			if(is_array($ids))
			switch($this->request->post('action'))
			{
			    case 'disable':
			    {
			    	foreach($ids as $id)
						$this->users->update_user($id, array('enabled'=>0));    
					break;
			    }
			    case 'enable':
			    {
			    	foreach($ids as $id)
						$this->users->update_user($id, array('enabled'=>1));    
			        break;
			    }
			    case 'delete':
			    {
			    	foreach($ids as $id)
						$this->users->delete_user($id);    
			        break;
			    }
			    case 'update_balance':
			    {
			    	// Обновление балансов выбранных пользователей
			    	foreach($ids as $id){
			    		$uid = $this->users->get_user_uid($id);

			    		if(($uid->uid!='Error') && ($uid->uid_status=='ok') && !empty($uid->uid)){

			    			$user_balance_1c = $this->users->get_user_balance_1c($uid->uid);
			    			$user_balance_1c = $this->users->make_up_user_balance($id, $user_balance_1c->return);			    			

			    			$user_balance = $this->users->get_user_balance($id);

			    			if(!$user_balance || empty($user_balance))
			    				$balance_id = $this->users->add_user_balance($user_balance_1c);
			    			else
			    				$balance_id = $this->users->update_user_balance($user_balance->id, $user_balance_1c);
						}
			    	}
			        break;
			    }
			}		
		}  

		foreach($this->users->get_groups() as $g)
			$groups[$g->id] = $g;
		
		
		$group = null;
		$filter = array();
		$filter['page'] = max(1, $this->request->get('page', 'integer')); 		
		$filter['limit'] = 20;

		$group_id = $this->request->get('group_id', 'integer');
		if($group_id)
		{
			$group = $this->users->get_group($group_id);
			$filter['group_id'] = $group->id;
		}
		
		// Поиск
		$keyword_phone = $this->request->get('keyword_phone', 'string');
		if(!empty($keyword_phone))
		{	
			$phone_replace = array('+','(',')',' ','-');
			$keyword_phone = str_replace($phone_replace,'',$keyword_phone);
			$filter['keyword_phone'] = $keyword_phone;
			$this->design->assign('keyword_phone', $keyword_phone);
		}	
		$keyword_surname = $this->request->get('keyword_surname', 'string');
		if(!empty($keyword_surname))
		{
			$filter['keyword_surname'] = $keyword_surname;
			$this->design->assign('keyword_surname', $keyword_surname);
		}	
		$keyword_name = $this->request->get('keyword_name', 'string');
		if(!empty($keyword_name))
		{
			$filter['keyword_name'] = $keyword_name;
			$this->design->assign('keyword_name', $keyword_name);
		}	
		$keyword_patronimic = $this->request->get('keyword_patronimic', 'string');
		if(!empty($keyword_patronimic))
		{
			$filter['keyword_patronimic'] = $keyword_patronimic;
			$this->design->assign('keyword_patronimic', $keyword_patronimic);
		}		
		
		// Сортировка пользователей, сохраняем в сессии, чтобы текущая сортировка не сбрасывалась
		if($sort = $this->request->get('sort', 'string'))
			$_SESSION['users_admin_sort'] = $sort;		
		if (!empty($_SESSION['users_admin_sort']))
			$filter['sort'] = $_SESSION['users_admin_sort'];			
		else
			$filter['sort'] = 'date';			
		$this->design->assign('sort', $filter['sort']);
		
		$users_count = $this->users->count_users($filter);
		// Показать все страницы сразу
		if($this->request->get('page') == 'all')
			$filter['limit'] = $users_count;	

		$users = $this->users->get_users($filter);
		foreach ($users as &$user) {
			$user->balance = $this->users->get_user_balance($user->id);
		}

		$this->design->assign('pages_count', ceil($users_count/$filter['limit']));
		$this->design->assign('current_page', $filter['page']);
		$this->design->assign('groups', $groups);		
		$this->design->assign('group', $group);
		$this->design->assign('users', $users);
		$this->design->assign('users_count', $users_count);
		return $this->body = $this->design->fetch('users.tpl');
	}
}
