<?php

require_once 'View.php';

class SmsTemplatesView extends View
{
    /**
     * ИД сайтов
     */
    const SITE_IDS = [
        'boostra',
        'neomani',
        'soyaplace'
    ];

    public function fetch()
    {
        $site_id = $this->request->get('site_id', 'string');

        if ($this->request->method('post'))
        {
            switch ($this->request->post('action', 'string')):
                
                case 'add':
                    
                    $name = trim($this->request->post('name'));
                    $template = trim($this->request->post('template'));
                    $type = trim($this->request->post('type'));
                    $check_limit = $this->request->post('check_limit', 'integer');
                    
                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название шаблона'));
                    }
                    elseif (empty($template))
                    {
                        $this->json_output(array('error' => 'Укажите текст сообщения'));
                    }
                    else
                    {
                        $dataInsert = [
                            'name' => $name,
                            'template' => $template,
                            'type' => $type,
                            'check_limit' => $check_limit,
                        ];

                        if ($site_id) {
                            $dataInsert['template_' . $site_id] = $template;
                        }

                        $id = $this->sms->add_template($dataInsert);

                        $this->json_output(array_merge($dataInsert, [
                            'id' => $id,
                            'success' => 'Шаблон сообщения добавлен'
                        ]));
                    }
                    
                break;
                
                case 'update':
                    
                    $id = $this->request->post('id', 'integer');
                    $name = trim($this->request->post('name'));
                    $template = trim($this->request->post('template'));
                    $type = trim($this->request->post('type'));
                    $check_limit = $this->request->post('check_limit', 'integer');
                    $status = $this->request->post('status', 'integer');

                    if (empty($name))
                    {
                        $this->json_output(array('error' => 'Укажите название шаблона'));
                    }
                    elseif (empty($template))
                    {
                        $this->json_output(array('error' => 'Укажите текст сообщения'));
                    }
                    else
                    {
                        $dataUpdate = [
                            'name' => $name,
                            'template' => $template,
                            'type' => $type,
                            'check_limit' => $check_limit,
                            'status' => $status,
                        ];

                        if ($site_id) {
                            unset($dataUpdate['template']);
                            $dataUpdate['template_' . $site_id] = $template;
                        }

                        $this->sms->update_template($id, $dataUpdate);
                        
                        $this->json_output(array_merge($dataUpdate, [
                            'id' => $id,
                            'success' => 'Шаблон обновлен'
                        ]));

                        $cacheKey = 'sms_template:' . (int)$id . ':' . ($site_id ?? 'null');
                        $this->caches->delete($cacheKey);
                    }
                    
                break;
                
                case 'delete':
                    
                    $id = $this->request->post('id', 'integer');
                    
                    $this->sms->delete_template($id);
                    
                    $this->json_output(array(
                        'id' => $id, 
                        'success' => 'Шаблон удален'
                    ));
                    
                break;
                
            endswitch;
        }
        
    	$sms_templates = $this->sms->get_templates();
        $this->design->assign('sms_templates', $sms_templates);
        
        $template_types = $this->sms->get_template_types();
        $this->design->assign('template_types', $template_types);

        if ($site_id) {
            $this->design->assign('template_value_field', 'template_' . $site_id);
        } else {
            $this->design->assign('template_value_field', 'template');
        }

        $this->design->assign('site_ids', self::SITE_IDS);
        $this->design->assign('site_id', $site_id);
        
        return $this->design->fetch('sms_templates.tpl');
    }
}