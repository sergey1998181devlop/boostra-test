<?php

chdir('..');
require 'api/Simpla.php';

class AssignedMissingManager extends Simpla
{
    private $response = [];

    public function __construct()
    {
        parent::__construct();
        $this->run();
        $this->json_output();
    }

    public function run()
    {
        $action = $this->request->get('action', 'string');

        switch ($action) {
            case 'check':
                $this->check_manager_permission();
                break;
            default:
                $this->response['error'] = 'Invalid or missing action';
        }
    }

    private function check_manager_permission()
    {
        $userId = $this->request->get('user_id');
        $managerId = $this->request->get('manager_id');

        if (!$userId) {
            $this->response['success'] = false;
            $this->response['error'] = 'User id not provided';
            return;
        }
        if (!$managerId) {
            $this->response['success'] = false;
            $this->response['error'] = 'Manager id not provided';
            return;
        }

        $user = $this->users->get_user($userId);

        if (!$user) {
            $this->response['success'] = false;
            $this->response['error'] = 'Пользователь не найден';
            return;
        }

        if (empty($user->missing_manager_id)) {
            $this->response['success'] = true;
            $this->response['allowed'] = true;
            $this->response['message'] = 'Client is free';
            return;
        }

        if ($user->missing_manager_id !== $managerId) {
            $this->response['success'] = false;
            $this->response['allowed'] = false;
            $this->response['error'] = 'Этот клиент закреплён за другим менеджером';
            return;
        }

        $this->response['success'] = true;
        $this->response['allowed'] = true;
        $this->response['message'] = 'Клиент принадлежит текущему менеджеру';
    }

    private function json_output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");

        echo json_encode($this->response);
    }
}

new AssignedMissingManager();