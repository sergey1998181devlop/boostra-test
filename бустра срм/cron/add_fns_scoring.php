<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';

// TODO удалить файл после выполнения
class AddFnsScoring extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        $ids = $_GET['ids'];

        if (empty($ids)) {
            echo 'укажите ids';
            exit;
        }

        $orders = $this->getOrders($ids);

        if (empty($orders)) {
            echo "<pre>";
            var_export('Заявки не найдены. Скоринги добавлены');
            die();
        }

        $values = [];
        foreach ($orders as $order) {
            $values[] = '(' . $order->user_id . ', "' . $order->id . '", ' . $this->scorings::STATUS_NEW . ', "' .
                date('Y-m-d H:i:s') . '", ' . $this->scorings::TYPE_FNS . ')';
        }

        $values = implode(', ', $values);
        $this->addFnsScorings($values);

        $this->logging(__METHOD__, '', '', ['Заявки, которым добавлен скоринг ФНС' => $orders], 'add_fns_scoring.txt');

        echo "<pre>";
        var_export(['Заявки, которым добавлен скоринг ФНС' => $orders]);
    }

    private function getOrders($ids): array
    {
        $query = "SELECT o.id, o.user_id
            FROM s_orders AS o
            WHERE NOT EXISTS (
                SELECT 1
                FROM s_scorings AS s
                WHERE o.id = s.order_id
                AND s.type = " . $this->scorings::TYPE_FNS . "
                AND s.status in (" . implode(',', [$this->scorings::STATUS_COMPLETED, $this->scorings::STATUS_PROCESS, $this->scorings::STATUS_NEW])  . ")
            )
        AND o.id in ($ids)";

        $this->db->query($query);
        return $this->db->results();
    }

    private function addFnsScorings($values)
    {
        $sql = "INSERT INTO s_scorings (`user_id`, `order_id`, `status`, `created`, `type`) 
        VALUES $values;";

        $this->db->query($sql);
    }
}

$cron = new AddFnsScoring();
$cron->run();
