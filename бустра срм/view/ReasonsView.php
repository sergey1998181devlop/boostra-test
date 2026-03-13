<?php

require_once 'View.php';

class ReasonsView extends View
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $admin_name = trim($this->request->post('admin_name'));
                    $client_name = trim($this->request->post('client_name'));
                    $maratory = intval($this->request->post('maratory'));
                    
                    if (empty($admin_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для администратора'));
                    }
                    elseif (empty($client_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для клиентов'));
                    }
                    else
                    {
                        $reason = array(
                            'admin_name' => $admin_name,
                            'client_name' => $client_name,
                            'type' => $this->request->get('type', 'string'),
                            'maratory' => $maratory
                        );
                        $id = $this->reasons->add_reason($reason);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'admin_name' => $admin_name, 
                            'client_name' => $client_name, 
                            'maratory' => $maratory,
                            'success' => 'Причина отказа добавлена'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $admin_name = trim($this->request->post('admin_name'));
                    $client_name = trim($this->request->post('client_name'));
                    $maratory = intval($this->request->post('maratory'));
                    
                    if (empty($admin_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для администратора'));
                    }
                    elseif (empty($client_name))
                    {
                        $this->json_output(array('error' => 'Укажите название для клиентов'));
                    }
                    else
                    {
                        $reason = array(
                            'admin_name' => $admin_name,
                            'client_name' => $client_name,
                            'maratory' => $maratory,
                        );
                        $this->reasons->update_reason($id, $reason);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'admin_name' => $admin_name, 
                            'client_name' => $client_name, 
                            'maratory' => $maratory,
                            'success' => 'Причина отказа обновлена'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->reasons->delete_reason($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Причина отказа удалена'
                    ));
                    
                break;
                
            endswitch;
        }
        
        if (!($type = $this->request->get('type', 'string')))
            return false;
        
    	$this->design->assign('type', $type);
        
        $reasons = $this->reasons->get_reasons(array('type' => $type));
        $this->design->assign('reasons', $reasons);
        
        return $this->design->fetch('reasons.tpl');
    }
    
    
}