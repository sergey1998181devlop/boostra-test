<?php

class Fms extends Simpla
{
    private $api_url = 'http://services.fms.gov.ru/info-service.htm?sid=2000';
    private $cookie_dir = 'files/scorings/cookies/';
    private $captcha_dir = 'files/scorings/captcha/';
    
    private $session_id = null;
    
    private $user_id;
    private $audit_id;
    private $order_id;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->cookie_dir = $this->config->root_dir.$this->cookie_dir;
        $this->captcha_dir = $this->config->root_dir.$this->captcha_dir;

        $this->session_id = md5(rand().microtime());
        
    }
    
    public function run_scoring($scoring_id)
    {
        $scoringType = $this->scorings->get_type($this->scorings::TYPE_FMS);
        if (empty($scoringType->active)) {
            return $this->scorings->update_scoring($scoring_id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Проверка на стороне СРМ отключена',
                'end_date' => date('Y-m-d H:i:s')
            ]);
        }

        $update = [];
        
        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            $order_id = (int)$scoring->order_id;
            $passport_serial = '';
            if ($order_id != 0 && $order = $this->orders->get_order($order_id)) {
                if (empty($order->passport_serial)) {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не указаны серия и номер паспорта'
                    );
                } else {
                    $passport_serial = str_replace(array(' ', '-'), '', $order->passport_serial);
                }
            } else if ($user = $this->users->get_user($scoring->user_id)) {
                $passport_serial = $user->passport_serial;
            } else {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'Паспорт не указан'
                );
            }

            if (!empty($passport_serial)) {
                if (strpos($passport_serial, '-')) {
                    $passport = explode('-', $passport_serial);
                    $serial = $passport[0];
                    $number = $passport[1];
                } else {
                    $passport = str_replace(' ', '', $passport_serial);
                    $serial = substr($passport, 0, 4);
                    $number = substr($passport, 4, 6);
                }
                $resp = $this->check_passport($serial, $number);
                if (isset($resp['string_result'])) {
                    $update = array(
                        'status' => $this->scorings::STATUS_COMPLETED,
                        'body' => $resp['string_result'],
                        'string_result' => $resp['string_result'],
                        'success' => (int)$resp['success'],
                    );
                    
                    if (empty($resp['success'])) {
                        $scorista = $this->scorings->get_scorings(['type' => $this->scorings::TYPE_SCORISTA, 'order_id' => $scoring->order_id]) ?? [];
                        $scorista = reset($scorista);
                        if (!empty($scorista) && $scorista->status == $this->scorings::STATUS_NEW) {
                            $this->scorings->update_scoring($scorista->id, ['status' => $this->scorings::STATUS_STOPPED]);
                        }
                    }
                
                } else {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'При запросе произошла ошибка'
                    );
                    if (empty($resp['success'])) {
                        $scorista = $this->scorings->get_scorings(['type' => $this->scorings::TYPE_SCORISTA, 'order_id' => $scoring->order_id]) ?? [];
                        $scorista = reset($scorista);
                        if (!empty($scorista) && $scorista->status == $this->scorings::STATUS_NEW) {
                            $this->scorings->update_scoring($scorista->id, ['status' => $this->scorings::STATUS_STOPPED]);
                        }
                    }
                }
            }

            if (!empty($update)) {
                $update['end_date'] = date('Y-m-d H:i:s');
                $this->scorings->update_scoring($scoring_id, $update);
            }
            return $update;
        }
    }
    
    public function run($audit_id, $user_id, $order_id)
    {
        $this->user_id = $user_id;
        $this->audit_id = $audit_id;
        $this->order_id = $order_id;
        
        $this->type = $this->scorings->get_type($this->scorings::TYPE_FMS);
    	
        $user = $this->users->get_user((int)$user_id);
        
        return $this->scoring($user->passport_serial);
    }


    private function scoring($passport)
    {
        $passport_serial = str_replace(array(' ', '-'), '', $passport);
        $serial = substr($passport_serial, 0, 4);
        $number = substr($passport_serial, 4, 6);
        $resp   = $this->check_passport($serial, $number);
        if (isset($resp['string_result'])) {
            $add_scoring = array(
                'user_id' => $this->user_id,
                'audit_id' => $this->audit_id,
                'type' => $this->scorings::TYPE_FMS,
                'body' => $resp['string_result'],
                'string_result' => $resp['string_result'],
                'success' => (int)$resp['success'],
            );
            $this->scorings->add_scoring($add_scoring);
        }
        
        return $resp['success'];
    }

    public function check_passport($serial, $number)
    {
        $data = [
            'passport_series' => $serial,
            'passport_number' => $number
        ];
        $update = [
            'success' => 0,
            'string_result' => null,
        ];
        $result = $this->infosphere->check_fms($data);

        if (isset($result['Source']) && $result['Source']['ResultsCount'] > 0) {
            foreach ($result['Source']['Record'] as $source) {
                foreach ($source as $field) {
                    if ($field['FieldName'] == 'ResultCode') {
                        if ($field['FieldValue'] == 'VALID') {
                            return [
                                'success' => 1,
                                'string_result' => 'Паспорт корректный',
                            ];
                        } else {
                            return [
                                'success' => 0,
                                'string_result' => 'Паспорт некорректный',
                            ];
                        }
                    }
                }
            }
        }
        return $update;
    }
    
    public function send($url, $data = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (!is_null($data))
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
echo 'FMS:'.$result;                
        return $result;
    }
}