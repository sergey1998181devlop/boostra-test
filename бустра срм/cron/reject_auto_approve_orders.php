<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '1200');

require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * Переводит автоодобренные заявки в отказ по истечению срока
 * Class RejectAutoApproveOrders
 */
class RejectAutoApproveOrders extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        $this->rejectOrders();
        $this->rejectTasks();
    }

    /**
     * Выполняет автоотказы для заявок
     * @return void
     */
    public function rejectOrders()
    {
        $sql = "SELECT 
            o.id,
            o.1c_id as id_1c,
            oaa.id as auto_approve_id
        FROM s_orders_auto_approve oaa 
        LEFT JOIN s_orders o ON o.id = oaa.order_id
        WHERE oaa.date_end < NOW()
            AND o.status NOT IN(3)
            AND o.credit_getted = 0";

        $this->db->query($sql);
        $orders = $this->db->results();
        $reason = $this->reasons->get_reason($this->reasons::REASON_AUTO_APPROVE);
        foreach ($orders as $order) {
            $response = $this->soap->update_status_1c(
                $order->id_1c,
                $this->orders::ORDER_1C_STATUS_REJECTED,
                '',
                0,
                1,
                $reason->admin_name
            );
            if ($response === 'OK') {
                $this->orders->update_order(
                    $order->id,
                    [
                        'status' => $this->orders::ORDER_STATUS_CRM_REJECT,
                        '1c_status' => $this->orders::ORDER_1C_STATUS_REJECTED_TECH,
                        'reason_id' => $this->reasons::REASON_AUTO_APPROVE,
                        'reject_date' => date('Y-m-d H:i:s'),
                    ]
                );

                $this->orders_auto_approve->updateAutoApproveOrder(
                    (int)$order->auto_approve_id,
                    [
                        'status' => $this->orders_auto_approve::STATUS_ERROR,
                    ]
                );
            }
        }
    }

    /**
     * Закрывает зависшие в обработке задачи из `s_auto_approve_nk`
     * @return void
     */
    public function rejectTasks()
    {
        $this->db->query(
            'SELECT id FROM s_auto_approve_nk WHERE `status` = ? AND date_edit < NOW() - INTERVAL 120 MINUTE',
            $this->orders_auto_approve::STATUS_CRON_PROCESS
        );

        $oldTasksId = $this->db->results('id');

        if ($oldTasksId) {
            foreach ($oldTasksId as $id) {
                $query = $this->db->placehold("
                UPDATE s_auto_approve_nk 
                SET status = ?, date_edit = NOW()
                WHERE id = ?
            ", $this->orders_auto_approve::STATUS_CRON_ERROR_TIMEOUT, $id);

                $this->db->query($query);
            }
        }
    }
}

(new RejectAutoApproveOrders())->run();
