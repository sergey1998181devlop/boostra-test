<?php

require_once 'View.php';



/**
 * AcadoView
 * 
    
    https://www.boostra.ru/acado/import
    POST
    параметры
    key = ZWM5MmE4YWY5OTQ4YmY2YzM2Y
    passport_series - серия паспорта 4 цифры
    passport_number - номер паспорта 6 цифр
    lastname - Фамилия клиента
    firstname - Имя клиента
    patronymic - Отчество клиента
    birth - дата рождения клиента Y-m-d
    phone - телефон клиента, только цифры начиная с 7
 */
class AcadoView extends View
{
    private $secret_key = 'ZWM5MmE4YWY5OTQ4YmY2YzM2Y';
    private $utm_source = 'acado';
    
    public function fetch()
    {
        $this->logging(__METHOD__, '', $_GET, $_POST, 'acado_request.txt');

    	$action = $this->request->get('action', 'string');
        switch ($action):
            
            case 'import':
                return $this->import_action();
                break;
                
        endswitch;
    }
    
    private function import_action()
    {
        $params = $this->get_import_params();
        
        if ($error = $this->check_params($params)) {
            return $this->display_error($error);
        }
        
        $acado_uid = $this->soap->get_acado_uid($params['passport_series'], $params['passport_number']);
        if ($acado_uid && $acado_uid == 'Множество совпадений') {
            return $this->display_error('ACADO_3333');
        } elseif ($acado_uid && $acado_uid != 'Не найден') {
            
            if ($user = $this->users->get_user_by_uid($acado_uid)) {
                if ($this->check_phone($user->phone_mobile, $params['phone'])) {
                    $this->go2lk($user->id);
                } else {
                    return $this->display_error('ACADO_1002');
                }
            
            } elseif ($user_id = $this->users->get_phone_user($params['phone'])){
                $user = $this->users->get_user((int)$user_id);
                
                if ($this->check_passport($user, $params) || empty($user->passport_serial)) {
                    $this->complete_registration($user, $acado_uid, $params);
                    $this->go2lk($user->id);
                } else {
                    return $this->display_error('ACADO_1004');
                }
                
            } else {

                $user_id = $this->users->add_user([
                    'UID' => $acado_uid,
                    'UID_status' => "ok",
                    'phone_mobile' => $params['phone'],
                    'utm_source' => $this->utm_source,
    				'utm_medium' => '',
                    'enabled' => 1,
                    'last_ip'=>$_SERVER['REMOTE_ADDR'],
                    'use_b2p' => 1,
                ]);

                if (empty($user_id)) {
                    return $this->display_error('ACADO_1003');                    
                } else {
                    $details = $this->soap->get_client_details($acado_uid);
                    $this->import1c->import_user($user_id, $details);
                    
                    $this->import_photos($user_id, $params);
                    
                    $this->go2lk($user_id);
                }
            }
            
        } else {
            $this->go2homepage();
        }
    }
    
    private function complete_registration($user, $acado_uid, $params)
    {
        $details = $this->soap->get_client_details($acado_uid);
        $this->import1c->import_user($user->id, $details);
        
        $this->import_photos($user->id, $params);
        
        $update = [
            'UID' => $acado_uid,
            'UID_status' => "ok",
            'utm_source' => $this->utm_source,
            'use_b2p' => 1,
            'last_ip'=>$_SERVER['REMOTE_ADDR'],
        ];
        if (empty($user->personal_data_added)) {
            $update['personal_data_added'] = 1;
            $update['personal_data_added_date'] = date('Y-m-d H:i:s');
        }
        if (empty($user->address_data_added)) {
            $update['address_data_added'] = 1;
            $update['address_data_added_date'] = date('Y-m-d H:i:s');
        }
        if (empty($user->accept_data_added)) {
            $update['accept_data_added'] = 1;
            $update['accept_data_added_date'] = date('Y-m-d H:i:s');
        }
        if (empty($user->additional_data_added)) {
            $update['additional_data_added'] = 1;
            $update['additional_data_added_date'] = date('Y-m-d H:i:s');
        }
        if (empty($user->files_added)) {
            $update['files_added'] = 1;
            $update['files_added_date'] = date('Y-m-d H:i:s');
        }
        if (empty($user->card_added)) {
            $update['card_added'] = 1;
            $update['card_added_date'] = date('Y-m-d H:i:s');
        }
    
        if (!empty($update)) {
            $this->users->update_user($user->id, $update);
        }
    }
    
    private function go2homepage()
    {
        header('Location:'.$this->config->root_url);
        exit;    	
    }
    
    
    private function go2lk($user_id) 
    {
        $_SESSION['user_id'] = $user_id;
        setcookie('user_id', $user_id, time() + 86400 * 365, '/');
                
        header('Location:'.$this->config->root_url.'/user');
        exit;
    }
    
    private function check_params($data)
    {
        if (empty($data['key']) || $data['key'] != $this->secret_key) {
            return 'ACADO_1010';
        }

        if (empty($data['passport_series'])) {
            return 'ACADO_1011';
        }
        if (empty($data['passport_number'])) {
            return 'ACADO_1012';
        }
        if (empty($data['phone'])) {
//            return 'ACADO_1013';
        }
    }
    
    private function check_phone($phone1, $phone2)
    {
        $format_phone1 = $this->soap->format_phone($phone1);
        $format_phone2 = $this->soap->format_phone($phone2);
        
        return $format_request_phone == $format_user_phone;
    }
    
    private function check_passport($user, $params)
    {
        $user_passport = str_replace(['-', ' '], '', $user->passport_serial);
        $request_passport = $params['passport_series'].$params['passport_number'];
        
        return $user_passport == $request_passport;
    }
    
    private function get_import_params()
    {
        $data = [];
        
        $data['passport_series'] = str_replace(['-', ' '], '', $this->request->get('passport_series'));
        $data['passport_number'] = str_replace(['-', ' '], '', $this->request->get('passport_number'));
        $data['firstname'] = $this->request->get('firstname');
        $data['lastname'] = $this->request->get('lastname');
        $data['patronymic'] = $this->request->get('patronymic');
        $data['birth'] = $this->request->get('birth');
        $data['phone'] = $this->request->get('phone');
        $data['key'] = $this->request->get('key');
        
        return $data;
    }
    
    private function display_error($code)
    {
        $html = '<section id="info">';
        $html .= '<div>';
        $html .= '<div class="box">';
        $html .= '<div style="color:red;text-align:center">';
        $html .= '<h1>Произошла ошибка</h1>';
        $html .= '<h3 style="color:black;">Свяжитесь с горячей линией по телефону указанному в шапке сайта.</h3><br /><br />';
        $html .= '<h3>Код ошибки: '.$code.'</h3>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</section>';
        
        return $html;
    }
    
    private function import_photos($user_id, $params)
    {
        $res = $this->get_acado_photos($params['passport_series'], $params['passport_number']);  
        if ($res->code == 200 && !empty($res->data->photos)) {
            foreach ($res->data->photos as $photo) {
                if ($photo->status == 'accept') {
                    $src = file_get_contents($photo->src);
                    $photo_path = $this->config->root_dir.$this->config->original_images_dir.$photo->name; 
                    file_put_contents($photo_path, $src);
                    $this->users->add_file( [
                        'user_id' => $user_id,
                        'name'    => $photo->name,
                        'type'    => $photo->type,
                        'status'  => 2,
                    ]);
                }
            }
            $this->soap->soap_send_files($user_id);
            
            $this->users->update_user($user_id, ['file_uploaded' => 1]);
        }
    }
    
    private function get_acado_photos($passport_series, $passport_number)
    {
        $link = 'https://crm.acado.market/api/clients/photos';
        $query = http_build_query([
            'key' => $this->secret_key,
            'passport_series' => $passport_series,
            'passport_number' => $passport_number,
        ]);
        $url = $link.'?'.$query;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_res = curl_exec($ch);
        $res = json_decode($json_res);

        return $res;
    }
}