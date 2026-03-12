<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';

class CalculateCD extends Simpla
{
    public function analyzeActions()
    {
        $action = $this->request->post('action', 'string');

        if ($action == 'update_slider_values') {
            $this->updateValue();
        }
    }

    private function updateValue()
    {
        $simpla = new Simpla();
        $user_id = $simpla->request->post('user');

        $last_order = $this->orders->get_last_order($user_id);
        $is_new_client = !$last_order;
        $amount = $this->request->post('amount');

        $result = $this->credit_doctor->getCreditDoctor($amount, $is_new_client);

        $result->price = $this->credit_doctor->numberToWords($result->price);

        echo json_encode(['success' => true, 'result' => $result]);
    }

}

(new CalculateCD())->analyzeActions();
