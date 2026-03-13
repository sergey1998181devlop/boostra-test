<?php

/**
 * Изменяем сумму в заявках где прошла скориста
 * Не запускать повторно!!!!
 */

error_reporting(E_ERROR);
ini_set('display_errors', 'on');

require_once dirname(__DIR__) . '/api/Simpla.php';

class FixOrder extends Simpla
{
    /**
     * Закешированный менеджер для цикла
     */
    private $manager;

    /**
     * Лимит заявок в цикле
     */
    public const LIMIT = 20;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

        ini_set("log_errors", 1);
        ini_set("error_log", __DIR__ . "/php-error.log");
        set_time_limit(0);
    }

    public function run()
    {
        $orders = $this->getOrders();
        foreach ($orders as $key => $order) {
            echo "Process: " . ($key + 1) . " FROM " . count($orders) . " (" . $order->order_id . ")" . PHP_EOL;

            // проверим актуальный статус 1С
            $order_data = $this->orders->get_order($order->order_id);
            if (!in_array($order_data->status_1c, [
                $this->orders::ORDER_UPDATE_1C_STATUS_NEW,
                $this->orders::ORDER_1C_STATUS_APPROVED,
            ])) {
                $this->addCompletedOrder((int)$order->order_id);
                continue;
            }

            $scoring = $this->scorings->get_last_scorista_for_order($order->order_id, true);

            if (empty($scoring)) {
                $scoring = $this->addScorista($order);
            }

            if (!empty($scoring)) {
                $result_scorista = json_decode($scoring->body);
                $amount = min((int)$result_scorista->additional->decisionSum, 9800) ?: 9800;

                if ($scoring->scorista_status === 'Отказ') {
                    $res = $this->soap->set_tehokaz($order->id_1c);
                    if ($res === 'OK') {
                        $this->orders->update_order(
                            (int)$order->order_id,
                            [
                                'status' => 3,
                                'reason_id' => $this->reasons::REASON_CLOSED_ONE_DIVIDE_ORDER_REASON_ID,
                                '1c_status' => '7.Технический отказ',
                                'reject_date' => date('Y-m-d H:i:s'),
                            ]
                        );
                        $this->addCompletedOrder((int)$order->order_id);
                    }
                } else {
                    if ($order->amount == $amount) {
                        $this->addCompletedOrder((int)$order->order_id);
                        continue;
                    }

                    $this->orders->update_order($order->order_id, ['amount' => $amount]);
                    $this->changelogs->add_changelog(
                        [
                            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                            'created' => date('Y-m-d H:i:s'),
                            'type' => 'amount',
                            'old_values' => $order->amount,
                            'new_values' => $amount,
                            'order_id' => $order->order_id,
                            'user_id' => $order->user_id,

                        ]
                    );

                    $this->soap->update_status_1c(
                        $order->id_1c,
                        'Одобрено',
                        $this->manager->name,
                        $amount,
                        $order->percent,
                        '',
                        0,
                        $order->period
                    );

                    if (!$this->hasSMS((int)$order->order_id)) {
                        // отправим смс с новой суммой
                        $user = $this->users->get_user($order->user_id);
                        $this->smssender->sendApprovedSms($user, $order->order_id, $amount);
                        $this->finroznica->send_user($user);
                    }

                    $this->addCompletedOrder((int)$order->order_id);
                }
            }
        }
    }

    /**
     * Добавляет запись о том что заявка обработана
     * @param int $order_id
     * @return mixed
     */
    private function addCompletedOrder(int $order_id)
    {
        $query = $this->db->placehold("INSERT INTO s_cron_fixed_divide_amount SET ?%", compact('order_id'));
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * @param $order_id
     * @return bool
     */
    private function hasSMS($order_id)
    {
        $sql = "SELECT EXISTS(SELECT * FROM s_sms_messages WHERE order_id = ? AND type = ?) as r";
        $this->db->query($this->db->placehold($sql, $order_id, $this->smssender::TYPE_AUTO_APPROVE_ORDER));
        return (bool)$this->db->result('r');
    }

    private function addScorista($order)
    {
        $scoring = array(
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'type' => $this->scorings::TYPE_SCORISTA,
            'status' => $this->scorings::STATUS_NEW,
        );
        $this->scorings->log_add_scoring('fix_divide_orders.php', $scoring);
        if ($scoring_id = $this->scorings->add_scoring($scoring)) {
            $this->scorings->update_scoring($scoring_id, array(
                'status' => $this->scorings::STATUS_PROCESS,
                'start_date' => date('Y-m-d H:i:s')
            ));

            $this->scorista->run_scoring($scoring_id, true);
            $scoring = $this->scorings->get_scoring($scoring_id);
        }

        if ($scoring->status === $this->scorings::STATUS_COMPLETED && (int)$scoring->success === 1) {
            return $scoring;
        } else {
            return false;
        }
    }

    private function getOrders()
    {
        $filter = [
            'divide_statuses' => [
                $this->orders::DIVIDE_ORDER_STATUS_NEW,
                $this->orders::DIVIDE_ORDER_STATUS_ADD_NEW_ORDER,
                $this->orders::DIVIDE_ORDER_STATUS_APPROVED,
            ],
            '1c_statuses' => [
                $this->orders::ORDER_UPDATE_1C_STATUS_NEW,
                $this->orders::ORDER_1C_STATUS_APPROVED,
            ],
            'filter_date_start' => date('Y-m-d 00:00:00', strtotime('- 1 day')),
            'filter_date_end' => date('Y-m-d 23:59:59'),
        ];

        $sql = "SELECT 
                    do.status,
                    do.divide_order_id,
                    o.percent,
                    o.amount,
                    o.user_id,
                    o.id as order_id,
                    o.`1c_id` as id_1c,
                    o.`1c_status` as status_1c
                FROM 
                s_divide_order do 
                LEFT JOIN s_orders o ON o.id = do.divide_order_id
                LEFT JOIN s_cron_fixed_divide_amount cfd on cfd.order_id = o.id
                WHERE 1
                    AND cfd.id IS NULL 
                    AND do.auto_generate = 1 
                    AND do.status IN (?@)
                    AND o.`1c_status` IN (?@)
                    AND do.date_added BETWEEN ? AND ?
                ORDER BY do.id ASC   
                LIMIT " . self::LIMIT;

        $query = $this->db->placehold(
            $sql,
            $filter['divide_statuses'],
            $filter['1c_statuses'],
            $filter['filter_date_start'],
            $filter['filter_date_end']
        );
        $this->db->query($query);

        return $this->db->results();
    }
}

$start = microtime(true);
(new FixOrder())->run();
$end = microtime(true);

$time_worked = microtime(true) - $start;
exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
