<?php

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('max_execution_time', '600');

require_once __DIR__ . '/../api/Simpla.php';

/**
 * Отправка СМС одобренным заявкам
 * Class SendSmsToApprove
 */
class SendSmsToApprove extends Simpla
{

    public function getApproveOrders()
    {
        // получим заявки с Одобрено у которых дата одобрения < текущей даты на 1 или 2 дня
        $sql = "SELECT 
    datediff(NOW(), o.approve_date) as day_after_approve,
    u.phone_mobile,
    o.user_id,
    o.id as order_id,
    o.organization_id,
    IFNULL(o.approve_amount, o.amount) as amount,
    u.firstname
FROM s_orders o LEFT JOIN s_users u ON u.id = o.user_id
WHERE o.status = 2 AND o.confirm_date IS NULL AND o.1c_status != '5.Выдан' AND o.1c_status != '6.Закрыт'
AND NOT EXISTS (SELECT * FROM s_orders o2 WHERE o2.id > o.id AND o2.user_id = o.user_id)
AND datediff(NOW(), o.approve_date) BETWEEN 1 AND 7";

        $this->db->query($sql);
        return $this->db->results();
    }


    public function run()
    {
        if ($orders = $this->getApproveOrders()) {
            foreach ($orders as $order) {

                $site_id = $this->organizations->get_site_organization($order->organization_id);
                $this->settings->setSiteId($site_id, false);
                $settings_notice_sms_approve = $this->settings->notice_sms_approve;

                $key = (int)$order->day_after_approve;
                // пропускаем отправку тех кого отключили в лк
                if (in_array($key, [1,2,6,7]) && $this->blocked_adv_sms->getItemByUserId($order->user_id)) {
                    continue;
                }

                $status_send = !empty($settings_notice_sms_approve['message_day_'. $key]['status']);
                $text_template = $settings_notice_sms_approve['message_day_' . $key]['text'] ?? '';

                if (!empty($text_template)) {
                    $text = strtr($text_template, [
                        '{{firstname}}' => $order->firstname,
                        '{{amount}}' => $order->amount,
                    ]);
                }

                if (!empty($text) && !empty($status_send)) {
                    $site_id = $this->users->get_site_id_by_user_id($order->user_id);
                    $resp = $this->smssender->send_sms($order->phone_mobile, $text, $site_id);
                    $this->sms->add_message(
                        [
                            'user_id' => $order->user_id,
                            'order_id' => $order->order_id,
                            'phone' => $order->phone_mobile,
                            'message' => $text,
                            'created' => date('Y-m-d H:i:s'),
                            'send_status' => $resp[1],
                            'delivery_status' => '',
                            'send_id' => $resp[0],
                            'type' => $this->smssender::TYPE_APPROVE_ORDER,
                        ]
                    );
                }
            }
        }
    }
}

(new SendSmsToApprove())->run();
