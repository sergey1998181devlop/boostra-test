<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__FILE__).'/../api/Simpla.php';

class B2pResurveyCron extends Simpla
{
    public function __construct()
    {
        $this->run();
    }

    private function run()
    {
        if ($p2pcredits = $this->get_p2pcredits()) {
            foreach ($p2pcredits as $p2pcredit) {
                $this->check_status($p2pcredit);
            }
        }
    }


    private function check_status($p2pcredit)
    {
        $order = $this->orders->get_order($p2pcredit->order_id);
        $sector = $p2pcredit->body['sector'];

        $register_info = $this->best2pay->get_register_info($sector, $p2pcredit->register_id);
        $register_xml = simplexml_load_string($register_info);
        $register_status = (string)$register_xml->state;
        echo '<pre>';var_dump($register_info);
        if (empty($p2pcredit->operation_id)) {
            foreach ($register_xml->operations as $xml_operation)
//                if ($xml_operation->operation->state == 'APPROVED')
                $p2pcredit->operation_id = (string)$xml_operation->operation->id;
        }

        if (!empty($p2pcredit->operation_id)) {
            $info = $this->best2pay->get_operation_info($sector, $p2pcredit->register_id, $p2pcredit->operation_id);
            $xml = simplexml_load_string($info);
            $status = (string)$xml->state;
        }

        if ($register_status == 'EXPIRED') {
            $p2pcredit_date = date_create_from_format('Y.m.d H:i:s', (string)$register_xml->date)->format('Y-m-d H:i:s');
            $update_p2pcredit = [
                'response' => $register_info,
                'status' => $register_status,
                'complete_date' => $p2pcredit_date,
            ];
            $this->best2pay->update_p2pcredit($p2pcredit->id, $update_p2pcredit);
            if (empty($order->credit_getted) && !in_array($order->status, [10, 11])) {
                $this->orders->update_order($p2pcredit->order_id, [
                    'status' => 11,
                    'pay_result' => 'Ошибка: Время истекло',
                ]);
            }
            return;
        }

        if (isset($status) && $status == 'APPROVED')
        {
            $p2pcredit_date = date_create_from_format('Y.m.d H:i:s', (string)$xml->date);
            $update_p2pcredit = [
                'response' => $info,
                'operation_id' => (string)$xml->id,
                'complete_date' => $p2pcredit_date->format('Y-m-d H:i:s'),
            ];
            $this->best2pay->update_p2pcredit($p2pcredit->id, $update_p2pcredit);

            $this->issuance->issuanceByStatus($xml->state, $order, $xml);

            $this->best2pay->update_p2pcredit($p2pcredit->id, ['status' => $status]);
            return;
        }

        if (isset($status) && $status == 'REJECTED')
        {
            $p2pcredit_date = date_create_from_format('Y.m.d H:i:s', (string)$xml->date);
            $complete_date = $p2pcredit_date->format('Y-m-d H:i:s');
            $update_p2pcredit = [
                'response' => $info,
                'status' => ($register_status == 'REGISTERED' && empty($xml->operations)) ? 'REJECTED' : $status,
                'operation_id' => (string)$xml->id,
                'complete_date' => $complete_date,
            ];

            if ($have_new_order = $this->check_new_order($p2pcredit->order_id)) {
                echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('have_new_order', $have_new_order->id);echo '</pre><hr />';
                $this->orders->update_order($p2pcredit->order_id, [
                    'status' => 11,
                    'pay_result'=>'Ошибка: '.strval($xml->message)
                ]);
            } else {
                if ($exist_approved = $this->get_approve_p2pcredit($p2pcredit->order_id)) {
                    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('exist_approved', $exist_approved);echo '</pre><hr />';
                    $this->orders->update_order($p2pcredit->order_id, [
                        'status' => 11,
                        'pay_result'=>'Ошибка: '.strval($xml->message)
                    ]);

                } elseif (strtotime($complete_date) > strtotime(date('Y-m-d 00:00:00'))) {
                    $this->orders->update_order($p2pcredit->order_id, [
                        'status' => 8,
                        'pay_result'=>'Ошибка: '.strval($xml->message)
                    ]);
                    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('move_to_sign', $p2pcredit->order_id);echo '</pre><hr />';
                }
            }

            $this->best2pay->update_p2pcredit($p2pcredit->id, $update_p2pcredit);


            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($p2pcredit, $update_p2pcredit);echo '</pre><hr />';
        }
    }

    private function check_new_order($order_id)
    {
        $old_order = $this->orders->get_order((int)$order_id);
        $this->db->query("
            SELECT * FROM s_orders
            WHERE user_id = ?
            AND date > ?
            AND organization_id = ?
        ", $old_order->user_id, $old_order->date, $old_order->organization_id);

        return $this->db->result();
    }

    private function get_p2pcredits()
    {
        $this->db->query("
            select * from b2p_p2pcredits 
            where  status not in (
                'APPROVED', 'REJECTED', 'EXPIRED'
            )
            and date > '2026-01-01'
            and date < ?
            and register_id > 0
            limit 1000
        ", date('Y-m-d H:i:s', time() - 600));
        if ($results = $this->db->results()) {
            $results = array_map(function($e){
                $e->body = unserialize($e->body);
                return $e;
            }, $results);
        }
        echo '<pre>';print_r($results);
        return $results;
    }

    public function get_approve_p2pcredit($order_id)
    {
        $this->db->query("
            SELECT * FROM b2p_p2pcredits
            WHERE order_id = ?
            AND status = 'APPROVED'
        ", (int)$order_id);
        return $this->db->result();
    }

}
new B2pResurveyCron();