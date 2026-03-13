<?php

chdir('..');
require_once 'api/Simpla.php';

class ClearStopList extends Simpla {
    public function analyzeActions()
    {
        $action = $this->request->post('action', 'string');

        if ($action == 'stoplist') {
            $this->actionClearStoplist();
        }
    }

    /**
     * Remove client from Stop List
     * @return void
     */
    private function actionClearStoplist(): void
    {
        $userId = $this->request->post('user_id', 'integer');
        $user = $this->users->get_user($userId);

        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }

        $type = 'success';
        $this->sms->delete_records_by_phone($user->phone_mobile);
        $response = 'Клиент удален из стоп-листа';

        $this->sms->editSmsByPhone($user->phone_mobile, [
            'validated' => 0,
        ]);

        if (!$this->request->isAjax()) {
            header('Location: ' . $this->request->url());
        } else {
            $this->json_output([$type => $response]);
        }
    }

    protected function json_output($data)
    {
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }
}

(new ClearStopList())->analyzeActions();