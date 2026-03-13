<?php

namespace App\Containers\DomainSection\Orders\Actions;

use Generator;
use Orders;

require_once ROOT_DIR . '/api/Orders.php';
require_once ROOT_DIR . '/vendor/autoload.php';

/**
 * Исполнительный класс для тела CLI-команды 'UpdateOrdersByCustomerIP'
 */
class UpdateOrdersByCustomerIPAction extends Orders
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Запуск выполнения скрипта
     * @param string $ip
     * @param int $count
     * @return void
     */
    public function execute(string $ip, int $count)
    {
        $query = $this->db->placehold(
            'SELECT id, user_id FROM s_orders WHERE ip = ? AND status = 3 AND reason_id = 12 LIMIT ?',
            $ip,
            $count
        );

        foreach ($this->getOrders($query) as $order) {
            //Обновляем записи в БД
            $this->update_order($order->id, [
                'reason_id' => 10,
            ]);
            //Добавляем метку обновления заказа командой
            $this->order_data->set($order->id, 'TASK:help-73', 1);

            //Создаём автозаявку
            $this->orders_auto_approve->addAutoApproveNK([
                'user_id' => $order->user_id,
                'status' => $this->orders_auto_approve::STATUS_NEW,
                'date_cron' => date('Y-m-d H:i:s'),
            ]);

            echo 'Обновлена заявка ' . $order->id . '. User ID = ' . $order->user_id . PHP_EOL;
        }
    }

    /**
     * Получить заявки для обновления
     * @param string $query
     * @return Generator
     */
    private function getOrders(string $query): Generator
    {
        $this->db->query($query);

        $rows = $this->db->results() ?: [];
        foreach ($rows as $result) {
            yield $result ?: [];
        }
    }
}
