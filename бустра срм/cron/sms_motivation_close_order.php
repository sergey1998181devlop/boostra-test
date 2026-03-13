<?php

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('max_execution_time', '600');

require_once __DIR__ . '/../api/Simpla.php';

/**
 * Отправка СМС клиентам с мотивацией на закрытие
 * Class SmsMotivationCloseOrder
 */
class SmsMotivationCloseOrder extends Simpla
{

    public function getUsers()
    {
        /*
         * Договор открыт (действует), НК (ранее не было закрытых договоров займа),
         * ставка в договоре займа 0%, Просрочка = -10; -5; -1 (до Даты платежа, указанной в договоре займа остался 1 день, 5 дней или 10 дней ).
         * */
        $sql = "SELECT 
                    u.phone_mobile,
                    o.user_id,
                    o.id as order_id
                FROM s_orders o 
                    LEFT JOIN s_users u ON u.id = o.user_id
                    LEFT JOIN s_user_balance ub ON ub.user_id = o.user_id
                WHERE 
                    o.have_close_credits = 0
                    AND o.status = 2 
                    AND o.1c_status = '5.Выдан'
                    AND NOT EXISTS (SELECT * FROM s_orders o2 WHERE o2.date > o.date AND o2.user_id = o.user_id)
                    AND o.percent = 0
                    AND NOT EXISTS (SELECT * FROM s_sms_messages s WHERE s.type = ? AND s.user_id = o.user_id AND DATE(s.created) = CURDATE())
                    AND datediff(NOW(), ub.payment_date) IN (-10,-5,-1)";
        $query = $this->db->placehold($sql, $this->smssender::TYPE_MOTIVATION_CLOSED_ORDER);

        $this->db->query($query);
        return $this->db->results();
    }

    public function run()
    {
        $sms_template_motivation_close_status = $this->settings->sms_template_motivation_close_status;
        if (!empty($sms_template_motivation_close_status)) {
            $users = $this->getUsers();
            if (!empty($users)) {
                $sms_template_motivation_close = $this->sms->get_template($this->sms::SMS_TEMPLATE_MOTIVATION_CLOSE);
                foreach ($users as $user) {
                    $property = $sms_template_motivation_close->template . '_' . $user->site_id;
                    $resp = $this->smssender->send_sms($user->phone_mobile, $sms_template_motivation_close->$property, $user->site_id);
                    $this->sms->add_message(
                        [
                            'user_id' => $user->user_id,
                            'order_id' => $user->order_id,
                            'phone' => $user->phone_mobile,
                            'message' => $sms_template_motivation_close->template,
                            'created' => date('Y-m-d H:i:s'),
                            'send_status' => $resp[1],
                            'delivery_status' => '',
                            'send_id' => $resp[0],
                            'type' => $this->smssender::TYPE_MOTIVATION_CLOSED_ORDER,
                        ]
                    );
                }
            }
        }
    }
}

(new SmsMotivationCloseOrder())->run();