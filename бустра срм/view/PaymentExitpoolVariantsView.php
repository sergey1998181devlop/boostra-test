<?php

require_once 'View.php';

class PaymentExitpoolVariantsView extends View
{
    public function fetch()
    {
        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $variant = trim($this->request->post('variant'));
                    
                    if (empty($variant))
                    {
                        $this->json_output(array('error' => 'Укажите вариант ответа'));
                    }
                    else
                    {
                        $exitpool_variant = array(
                            'variant' => $variant,
                            'enabled' => 1,
                        );
                        $id = $this->payment_exitpools->add_variant($exitpool_variant);
                        $this->payment_exitpools->update_variant($id, array('position'=>$id));
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'variant' => $variant, 
                            'success' => 'Вариант ответа добавлен'
                        ));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $variant = trim($this->request->post('variant'));
                    
                    if (empty($variant))
                    {
                        $this->json_output(array('error' => 'Укажите вариант ответа'));
                    }
                    else
                    {
                        $exitpool_variant = array(
                            'variant' => $variant,
                        );
                        $this->payment_exitpools->update_variant($id, $exitpool_variant);
                        
                        $this->json_output(array(
                            'id' => $id, 
                            'variant' => $variant, 
                            'success' => 'Вариант ответа обновлен'
                        ));                        
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->payment_exitpools->delete_variant($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Вариант ответа удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
    	$variants = $this->payment_exitpools->get_variants();
        $this->design->assign('variants', $variants);
        

    	return $this->design->fetch('payment_exitpool_variants.tpl');
    }
    
}