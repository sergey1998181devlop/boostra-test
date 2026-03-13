<?php

require_once 'View.php';

class MaratoriumsView extends View
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $name = trim($this->request->post('name'));
                    $period = trim($this->request->post('period', 'integer'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название маратория'));
                    }
                    elseif (empty($period))
                    {
                        $this->json_output(array('error' => 'Укажите период действия маратория'));
                    }
                    else
                    {
                        $maratorium = array(
                            'name' => $name,
                            'period' => $period * 86400,
                        );
                        $id = $this->maratoriums->add_maratorium($maratorium);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'period' => $period, 
                            'success' => 'Мораторий добавлен'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    $period = trim($this->request->post('period', 'integer'));
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название маратория'));
                    }
                    elseif (empty($period))
                    {
                        $this->json_output(array('error' => 'Укажите период действия маратория'));
                    }
                    else
                    {
                        $maratorium = array(
                            'name' => $name,
                            'period' => $period * 86400,
                        );
                        $this->maratoriums->update_maratorium($id, $maratorium);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'name' => $name, 
                            'period' => $period, 
                            'success' => 'Мораторий обновлен'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->maratoriums->delete_maratorium($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Мораторий удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
    	$maratoriums = $this->maratoriums->get_maratoriums();
        $this->design->assign('maratoriums', $maratoriums);
        
        return $this->design->fetch('maratoriums.tpl');
    }
    
    
}