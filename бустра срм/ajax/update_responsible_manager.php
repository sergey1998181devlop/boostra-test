<?php

chdir('..');

require 'api/Simpla.php';

class UpdateResponsimbleManager extends Simpla
{
    public function run()
    {
        // проверка на данные
        $data = $this->request->get('data');
        if (!$data) {
            $this->response->json_output(['success' => false, 'message' => 'Data not found!']);
        }

        // поиск тел
        $user_id = $this->get_user_info_by_phone($data["phone"]);
        if (!$user_id) {
            $this->response->json_output(['success' => false, 'message' => 'Phone not found!']);
        }

        // поиск менеджера
        $manager_name = $this->get_manager_id($data['manager_id']);
        if (!$manager_name) {
            $this->response->json_output(['success' => false, 'message' => 'Manager not found!']);
        }


        $res_update_manager_id = $this->users->assign_manager($user_id, $data['manager_id']);


        $data_return = $res_update_manager_id ?
            [
                'success' => true,
                'message' => '[missing_manager_id] success updated',
                'user' => $user_id,
                'manager' => ['id' => $data['manager_id'], 'name' => $manager_name],

            ] : [
                'success' => false,
                'message' => '[missing_manager_id] failed to update'
            ];


        $this->response->json_output($data_return);
    }


    /**
     * Find phone in table users
     *
     * @param string $phone0
     * @return false|int
     */
    private function get_user_info_by_phone(string $phone)
    {
        $phone = $this->prepare_phone($phone);
        $query = $this->db->placehold(" 
            SELECT id FROM __users 
            WHERE phone_mobile = ?
        ", $phone);
        $this->db->query($query);
        return $this->db->result('id');
    }

    /**
     * 8 -> 7
     *
     * @param $phone
     * @return int
     */
    private function prepare_phone($phone): int
    {
        settype($phone, 'string');
        $phonePrepare = preg_replace('/\D+/iu', '', trim($phone));
        if ($phonePrepare[0] == '8') {
            $phonePrepare[0] = '7';
        }
        settype($phonePrepare, 'integer');
        return (integer)$phonePrepare;
    }

    /**
     * @param $manager_id
     * @return false|int
     */
    private function get_manager_id($manager_id)
    {
        $query = $this->db->placehold(" 
            SELECT name FROM __managers 
            WHERE id = ?
        ", (int)$manager_id);
        $this->db->query($query);
        return $this->db->result('name');
    }

}

(new UpdateResponsimbleManager())->run();

//use chats\main\Users as User;
//
//error_reporting(0);
//ini_set('display_errors', 'Off');
//date_default_timezone_set('Europe/Moscow');
//
//header('Content-type: application/json; charset=UTF-8');
//header('Cache-Control: must-revalidate');
//header('Pragma: no-cache');
//header('Expires: -1');
//
//session_start();
//chdir('..');
//
//require 'api/Simpla.php';
//
//$simpla = new Simpla();
//$data = $simpla->request->get('data');
//
//$response = [];
//$userData = [];
//if (!empty($data['listing_type'])) {
//    if ($data['listing_type'] === 'not_received_loans') {
//        $userData = ['not_received_loan_manager_id' => $data['manager_id']];
//    }
//
//    if ($data['listing_type'] === 'missings') {
//        $userData = [
//            'missing_manager_id' => $data['manager_id'],
//            'missing_manager_update_date' => date('Y-m-d H:i:s')
//        ];
//    }
//}
//
//$user_id = User::getUserInfoByPhone($data['phone'])->id;
//
//$id = $simpla->users->update_user($user_id, $userData);
//
//$manager = $simpla->managers->get_managers(['id' => $data['manager_id']]);
//$user = $simpla->users->get_user($id);
//$simpla->response->json_output(['manager' => $manager[0]->name, 'user' => $user]);
