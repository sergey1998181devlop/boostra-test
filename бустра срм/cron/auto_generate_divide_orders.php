<?php

error_reporting(E_ERROR);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 3300);
require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * Автогенерация деленных заявок
 * class AutoGenerateOrders
 */
class AutoGenerateOrders extends Simpla {

    /**
     * Лимит заявок на выборке
     */
    public const LIMIT = 100;

    /**
     * Закешированный менеджер для цикла
     */
    private $manager;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);
    }

    /**
     * Основная функция
     * @return void
     * @throws Exception
     */
    public function run()
    {
        $orders = $this->getOrders();
        foreach ($orders as $order) {
            if ($divide_id = $this->generateDivide($order))
            {
                $divide_order = $this->orders->getDivideOrderById($divide_id);
                $new_order = $this->orders->get_order($divide_order->divide_order_id);

                if (!empty($new_order) && $result_scorista = $this->runScorista($new_order))
                {
                    //additional->decisionSum - рекомендованная сумма с учетом ПДН
                    //additional->decisionSum_without_PTI - рекомендованная сумма без учета ПДН

                    $amount = min((int)$result_scorista->additional->decisionSum, 9800);
                    $this->orders->update_order($new_order->order_id, ['amount' => $amount]);
                    $this->changelogs->add_changelog(
                        [
                            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                            'created' => date('Y-m-d H:i:s'),
                            'type' => 'amount',
                            'old_values' => $new_order->amount,
                            'new_values' => $amount,
                            'order_id' => $new_order->order_id,
                            'user_id' => $new_order->user_id,

                        ]
                    );

                    $this->soap->update_status_1c($new_order->id_1c, 'Одобрено', $this->manager->name, $amount, $new_order->percent, '', 0, $new_order->period);

                    // отправим смс с новой суммой
                    $user = $this->users->get_user($new_order->user_id);
                    $this->smssender->sendApprovedSms($user, $new_order->order_id, $amount);
                    $this->finroznica->send_user($user);

                    // добавляем проверку на ЧС
                    $scoring_data = [
                        'user_id' => $new_order->user_id,
                        'status' => $this->scorings::STATUS_NEW,
                        'order_id' => $new_order->order_id,
                        'type' => $this->scorings::TYPE_BLACKLIST,
                        'created' => date('Y-m-d H:i:s'),
                    ];

                    if($scoring_id = $this->scorings->add_scoring($scoring_data)) {
                        $this->blacklist->run_scoring($scoring_id);
                    }
                }
            }
        }
    }

    /**
     * Проводим скористу
     * @param $order
     * @return mixed|null
     */
    private function runScorista($order)
    {
        $scoring_result = null;

        //$result = $this->scorista->create((int)$order->order_id, true);
        $scoring = array(
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'type' => $this->scorings::TYPE_SCORISTA,
            'status' => $this->scorings::STATUS_NEW,
        );
        $this->scorings->log_add_scoring('auto_generate_divide_orders.php', $scoring);
        if ($scoring_id = $this->scorings->add_scoring($scoring)) {
            $this->scorings->update_scoring($scoring_id, array(
                'status' => $this->scorings::STATUS_PROCESS,
                'start_date' => date('Y-m-d H:i:s')
            ));

            $this->scorista->run_scoring($scoring_id, true);
            $scoring = $this->scorings->get_scoring($scoring_id);

            if ($scoring->status === $this->scorings::STATUS_COMPLETED && (int)$scoring->success === 1) {
                $scoring_result = json_decode($scoring->body);
            }
        }

        return $scoring_result;
    }

    /**
     * Генерирует разделение
     * @param $order
     * @return mixed
     * @throws Exception
     */
    private function generateDivide($order)
    {
        $data_divide_order = [
            'user_id' => (int)$order->user_id,
            'main_order_id' => (int)$order->order_id,
            'status' => $this->orders::DIVIDE_ORDER_STATUS_NEW,
            'auto_generate' => 1,
        ];

        $divide_order_id = $this->orders->addDivideOrder($data_divide_order);
        $status_generate_divide = $this->orders->generate_divide_order($divide_order_id, $order->amount, false);

        return $status_generate_divide === 'APPROVE_TO_1C' ? $divide_order_id : false;
    }

    /**
     * Получает список главных заявок для генерации разделения
     * @return array|false
     */
    private function getOrders()
    {
        $sql = "SELECT
                    b.user_id,
                    o.id as order_id,
                    o.amount
                FROM s_user_balance b
                     LEFT JOIN s_orders o ON o.`1c_id` = b.zayavka
                WHERE
                  DATEDIFF(NOW(), b.payment_date) <= 3
                  AND o.date BETWEEN '2023-02-01 00:00:00' AND '2023-04-13 23:59:59'
                  AND o.utm_source != 'divide_order'
                  AND o.have_close_credits = 1
                  AND NOT EXISTS(SELECT * FROM s_divide_order d WHERE d.user_id = o.user_id)
                  AND NULLIF(b.zayavka, '') IS NOT NULL
                  AND o.id IS NOT NULL
                ORDER BY b.zaim_date DESC";

        if (!empty(self::LIMIT)) {
            $sql .= " LIMIT " . self::LIMIT;
        }

        $this->db->query($sql);
        return $this->db->results();
    }
}

$start = microtime(true);
(new AutoGenerateOrders())->run();
$end = microtime(true);

$time_worked = microtime(true) - $start;
exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
