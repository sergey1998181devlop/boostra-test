<?php

chdir('..');

require 'api/Simpla.php';

class DeleteKd extends Simpla
{
    /**
     * @return void
     */
    public function run()
    {
        $userId = $this->request->post('user_id');
        $orderId = $this->request->post('order_id');
        $managerId = $this->request->post('manager_id');
        $balance = $this->users->get_user_balance($userId);
        $response = $this->soap->deleteKd($balance->zaim_number);
        if ($response->return == 'Ок') {
            $this->orders->update_order($orderId,['deleteKD' => true]);
            $this->changelogs->add_changelog([
                'manager_id' => $managerId,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'SHKD',
                'old_values' => 'Включен',
                'new_values' => 'Отключен',
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
            $this->response->json_output("success");
            return;
        }
        $this->response->json_output(json_encode($response->return));



    }
}

(new DeleteKd())->run();

