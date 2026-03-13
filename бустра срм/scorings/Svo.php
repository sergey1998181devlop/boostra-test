<?php

class Svo extends Simpla
{
    public function run_scoring($scoring_id)
    {
        $update = array();

        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                if (empty($order->birth))
                {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не указана дата рождения'
                    );
                } elseif (empty($order->gender)) {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не указан пол'
                    );
                } else {
                    $user_age = date_diff(date_create(date('Y-m-d', strtotime($order->birth))), date_create(date('Y-m-d')));
                    $user_age_year = $user_age->y;
                    
                    $update = array(
                        'status' => $this->scorings::STATUS_COMPLETED,
                    );

                    if ($order->gender == 'male' && $user_age_year <= 35)
                    {
                        $update['success'] = 0;
                        $update['string_result'] = 'Проверка не пройдена: мужчина, возраст: '.$user_age_year;
                    }
                    else
                    {
                        $update['success'] = 1;
                        $update['string_result'] = 'Проверка пройдена';                        
                    }
                }
                
            }
            else
            {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                );
            }
            
            if (!empty($update))
            {
                $update['end_date'] = date('Y-m-d H:i:s');
                $this->scorings->update_scoring($scoring_id, $update);
            }
            return $update;

        }
    }
    
}