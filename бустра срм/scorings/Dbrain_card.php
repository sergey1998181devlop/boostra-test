<?php

class Dbrain_card extends Simpla
{
    private $scoring_type;
    private $scoring;
    private $order;
    private $file;
    private $card;
    private $response;
    private $task_id;

    private $try_counter = 20;

    private $card_params = [
        'number' => ['pan', 80],
    ];
    
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    public function run_scoring($scoring_id)
    {
    	$this->scoring_type = $this->scorings->get_type($this->scorings::TYPE_AXILINK_2);

        if ($this->scoring = $this->scorings->get_scoring($scoring_id)) {
            if ($this->order = $this->orders->get_order((int)$this->scoring->order_id)) {
                
                // TODO: проверяем наличие файла
                $this->file = $this->users->get_file($this->scoring->audit_id);

                $this->task_id = $this->dbrain_api->pull_fields($this->scoring->audit_id, $this->dbrain_api::DOC_CARD);

                if (!empty($this->task_id)) {

                    $this->response = $this->get_response();                    
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($this->response);echo '</pre><hr />';
                    return $this->handling();
                
                } else {
                    return $this->update_scoring($this->scorings::STATUS_ERROR, ['string_result' => 'Не удалось Создать запрос']);
                }

            }
            else
            {
                return $this->update_scoring($this->scorings::STATUS_ERROR, ['string_result' => 'Не найдена заявка']);
            }
        }
    }

    private function get_response()
    {
        do {
            sleep(3);
            
            $response = $this->dbrain_api->get_result($this->task_id);                        
            $this->try_counter--;
        } while ($this->try_counter > 0 && $response == 202);
        
        return $response;
    }
    
    private function get_result($response)
    {
        $msg = '';
        $success = 1;
        $this->card = $this->best2pay->get_card($this->order->card_id);
        if ($fields = $this->dbrain_api->get_custom_fields($response, $this->card_params, $this->dbrain_api::DOC_CARD)) {
            foreach ($fields as $fieldname => &$field) {
                if (!$this->compare_card_field($fieldname, $field)) {
                    $success = 0;
                    $field['success'] = 0;
                    $msg = 'Поле '.$fieldname.' не совпадает';
                }
                $field['order_value'] = $this->card->$fieldname;
            }
        } else {
            $success = 0;
        }
                
        if ($success) {
            $msg = 'Проверка карты пройдена.';
        } else {
            $msg = 'Проверка карты не пройдена. '.$msg;                    
        }
        
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($fields);echo '</pre><hr />';                
        
        return compact('fields', 'success', 'msg');
        
    }
    
    private function compare_card_field($fieldname, $field)
    {
        $success = 1;
        
        $card_value = mb_strtoupper($this->card->$fieldname, 'utf-8');
        $field_text = $field['text'];
        
        if ($fieldname == 'pan') {
            $field_text = substr($field_text, 0, 6).'******'.substr($field_text, 12, 4);
        }

        if ($card_value != $field_text || !$field['success']) {
            $success = 0;
        }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($fieldname, $field, $card_value, $field_text);echo '</pre><hr />';
        
        return $success;
    }
    
    private function handling()
    {
        if ($result = $this->get_result($this->response)) {
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';            
            $update = [
                'success' => $result['success'],
                'body' => json_encode($result['fields'], JSON_UNESCAPED_UNICODE),
                'string_result' => $result['msg'],
                'scorista_id' => $this->task_id,
            ];
            
            if (!empty($result['success'])) {
                $this->approve_file();
            }
            
            return $this->update_scoring($this->scorings::STATUS_COMPLETED, $update);

        } else {
            return $this->update_scoring($this->scorings::STATUS_ERROR, ['string_result' => 'Не удалось распарсить поля']);
        }
        
    }
    
    private function approve_file()
    {
        $this->users->update_file($this->file->id, [
            'status' => 2
        ]);
        
        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'images',
            'old_values' => serialize(['status'=>$this->file->status]),
            'new_values' => serialize(['status'=>2]),
            'user_id' => $this->order->user_id,
            'order_id' => $this->order->order_id,
            'file_id' => $this->file->id,
        ]);
    }
    
    private function update_scoring($status, $update = [])
    {        
        $update['status'] = $status;
        $update['end_date'] = date('Y-m-d H:i:s');

        $this->scorings->update_scoring($this->scoring->id, $update);

        return $update;        
    }
}