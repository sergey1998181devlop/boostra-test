<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__.'/../api/Simpla.php';


class PrecheckCron extends Simpla
{
    public function __construct()
    {
    	$this->run();
    }

    private function run()
    {
        if ($items = $this->get_items()) {
            $items = $this->filter_items($items);
            if (!empty($items)) {
                $i = 1000;
                foreach ($items as $item) {
                    if ($i > 0) {
                        $i--;
                        $this->repeat_issuance($item);
                    }
                }
            }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($items);echo '</pre><hr />';            
        }
    }

    private function repeat_issuance($item)
    {
        $this->orders->update_order($item->order_id, ['status' => 8]);
        $this->changelogs->add_changelog([
            'manager_id' => 50,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'repeat_issuance',
            'old_values' => serialize([]),
            'new_values' => serialize([]),
            'order_id' => $item->order_id,
            'user_id' => $item->user_id,
        ]);
    }

    private function filter_items($items)
    {
        foreach ($items as $key => $item) {
            if ($new_order = $this->check_new_order($item->order_id)) {
                unset($items[$key]);
            }
            if ($pending = $this->check_pending($item->order_id)) {
                unset($items[$key]);
            }
        }
        return $items;
    }

    private function check_new_order($order_id)
    {
        $old_order = $this->orders->get_order((int)$order_id);
        $query = $this->db->placehold("
            select * from s_orders
            where user_id = ?
            and date > ?
            and utm_source != 'cross_order'
        ", $old_order->user_id, $old_order->date, $order_id);
        $this->db->query($query);
        return $this->db->result();
    }

    private function check_pending($order_id)
    {
        $old_order = $this->orders->get_order((int)$order_id);
        $this->db->query("
            select * from b2p_p2pcredits
            where order_id = ?
            and (
                status in ('TIMEOUT', 'PENDING')
                or response = '".'s:0:"";'."
            )
        ", $order_id);

        return $this->db->result();
    }

    private function get_items()
    {
        $border_date = date('Y-m-d 00:00:00');
        $complete_border_date = date('Y-m-d H:i:s', time() - 30*60);
        $this->db->query("
            select b.* from b2p_p2pcredits as b
            left join s_orders as o on o.id = b.order_id
            where b.status = 'PRECHECK'
            and b.date > ?
            and b.complete_date < ?
            and o.credit_getted = 0
            and o.status = 11
            and (
                o.pay_result = 'Ошибка выдачи: Bank acquiring request forbidden'
                or o.pay_result = 'Ошибка выдачи: Internal error'
            )
            group by b.order_id
            order by b.complete_date desc
        ", $border_date, $complete_border_date);
        return $this->db->results();
    }
}
new PrecheckCron();