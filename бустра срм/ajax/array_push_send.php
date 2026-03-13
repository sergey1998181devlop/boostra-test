<?php

chdir('..');

require 'api/Simpla.php';

class PushUsers extends Simpla
{
    const API_KEY = '1ub9NB2oQ4rLJMFnYxsqNjdCuVxIFHCoDqIfq6b8btBlSqQnHWMvEM2qS8OP0Ye9AZSbhHcBOGqwO9dTphgHUxdbqZY1S2wS2ufDYsB7booL8kq2ThTpEThVRZzzoacnSYCFmOsGvIXNGgqKDOOAaCOW9z1FT45q1p2zZjhF2U8i7SGXgyu0yxVUruyRbK3gM9R6kyXwJRWCbdOGC34iCrRGP2Q86z915vHjWCcVcn0cPgR8eAA0UIrtWnt6gbdf';
    const URL = 'https://apimp.boostra.ru/api/push/';

    public function __construct()
    {
        parent::__construct();
        $this->run();

    }

    public function run()
    {
        $dataPost = $this->request->post();
        $limit = $this->request->post('limit');
        $managerId = $this->request->post('manager');
        if ($limit == 'false') {
            $limit = false;
        }else{
            $limit = true;
        }
        parse_str($dataPost, $dataPost);
        $sendCount = $this->sendPush($dataPost,$managerId,$limit);
        echo json_encode(['success' => true, 'count' => $sendCount]);
    }

    private function sendPush($dataPost,$managerId,$limit) {
        $template = $this->sms->get_template(8);
        $data = [
            'user_id' => $dataPost['users_ids'],
            'title' => 'New new',
            "description" => $template->template,
            'for_all' => false
        ];
        $count_users = count($dataPost['users_ids']);
        $data['user_id'] = array_map('intval', $data['user_id']);
        $data =  json_encode($data);
        $response = $this->send(self::URL.'send','POST',$data);
        $count_not_sended = $response->result ? count($response->result->without_active_token_users) : $count_users;
        $count_sms = 0;
        foreach ($dataPost['users_ids'] as $user_id) {
            if (!in_array($user_id, $response->result->without_active_token_users)) {
                $this->addPush($user_id,$template->id, $managerId, $limit);
            }
        }
        if (!empty($response->result->without_active_token_users)){
            $users = $response->result->without_active_token_users;
            foreach ($users as $user) {
                $balance = $this->users->get_user_balance($user);
//                $resp =  $this->megafon->sendMessage($user, $managerId, 8, $balance,$limit);
//                if($resp){
                    $count_sms++;
//                }

            }
        }
        return ['pushes' => $count_users - $count_not_sended, 'sms' => $count_sms];
    }
    private function send($url, $method, $data = null) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS =>$data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Api-Key:'.self::API_KEY ,
                'Content-Type: application/json'
            ),
        ));



        $response = curl_exec($curl);
        return json_decode($response, false, 512, JSON_UNESCAPED_UNICODE);
    }

    private function addPush($userId, $template, $managerId, $limit)
    {
        $type = 'ccprolongation';
        if (!$limit) {
            $type = 'ccprolongation_zero';
        }
        $this->sms->add_push(array(
            'user_id' => $userId,
            'manager_id' => $managerId,
            'template' => $template,
            'created' => date('Y-m-d H:i:s'),
            'type' => $type
        ));
    }

}

(new PushUsers());