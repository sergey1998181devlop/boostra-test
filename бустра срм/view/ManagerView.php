<?php

require_once 'View.php';

class ManagerView extends View
{
    private $access_blocked_managers = [
        1, // Виталий Колготин
        3, // Руслан Копыл
        297, // Вадим Равилов
    ];
    
    public function fetch()
    {
        $id = $this->request->get('id', 'integer');
    	if ($this->request->method('post'))
        {
            $user = new StdClass();
            $user_id = $this->request->post('id', 'integer');

            if ($user_id != $this->manager->id && !in_array('managers', $this->manager->permissions))
            	return $this->design->fetch('403.tpl');

            
            $user->role = $this->request->post('role');
            $user->name = $this->request->post('name');
            $user->name_1c = $this->request->post('name_1c');
            $user->login = $this->request->post('login');
            $user->mango_number = $this->request->post('mango_number');
            
            if ($this->request->post('password'))
                $user->password = $this->request->post('password');
            
            $errors = array();
            
            if (empty($user->role))
                $errors[] = 'empty_role';
            if (empty($user->name))
                $errors[] = 'empty_name';
            if (empty($user->login))
                $errors[] = 'empty_login';
            
            if (empty($user_id) && empty($user->password))
                $errors[] = 'empty_password';
            
            $this->design->assign('errors', $errors);
            
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($_POST, $errors);echo '</pre><hr />';
            if (empty($errors))
            {
                if (!in_array('managers', $this->manager->permissions))
                    unset($user->role);

                if (empty($user_id))
                {
                    $user->id = $this->managers->add_manager($user);
                    $this->design->assign('message_success', 'added');

                    $this->changelogs->add_changelog(array(
                        'manager_id' => $this->manager->id,
                        'created' => date('Y-m-d H:i:s'),
                        'type' => 'create_manager',
                        'old_values' => '',
                        'new_values' => 'Создан менеджер: '.$user->id,
                        'order_id' => 0,
                        'user_id' => 0,
                    ));

                } else {
                    $user->id = $this->managers->update_manager($user_id, $user);
                    $this->design->assign('message_success', 'updated');
                }
            }
        }
        else
        {
            if ($id = $this->request->get('id', 'integer'))
            {
                $user = $this->managers->get_manager($id);
            }

            if ($id != $this->manager->id && !in_array('managers', $this->manager->permissions))
            	return $this->design->fetch('403.tpl');
            
        }
        
        if (in_array($this->manager->id, $this->access_blocked_managers)) {
            
            $this->design->assign('access_blocked_managers', $this->access_blocked_managers);
            
            if ($this->request->get('action') == 'blocked') {
                $this->managers->update_manager($user->id, [
                    'blocked' => $this->request->get('blocked', 'integer')
                ]);
                header('Location: '.$this->request->url(['blocked' => NULL, 'action' => NULL]));
            }
        }
        
        if (!empty($user))
        {
            $meta_title = 'Профиль '.$user->name;
            $this->design->assign('user', $user);

            $orders = $this->orders->get_orders(array('manager_id'=>$user->id, 'limit' => 50));
            $this->design->assign('orders', $orders);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($orders);echo '</pre><hr />';
        }
        else
        {
            $meta_title = 'Создать новый профиль';
        }

        $roles = $this->managers->get_roles();
        $this->design->assign('roles', $roles);
        
        
        $this->design->assign('meta_title', $meta_title);
        
        return $this->design->fetch('manager.tpl');
    }
    
}