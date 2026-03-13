<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__ . '/../api/Simpla.php';

class SendSmsMoratoriumCron extends Simpla
{
    // время задержки при отправке смс в секундах
    private $delay = 600;

    public function __construct()
    {
        parent::__construct();

        $this->run();
    }

    public function run()
    {
        $currentHour = (int)date('G');

        // Проверяем, находится ли текущий час в диапазоне с 10 до 13
        if ($currentHour >= 10 && $currentHour < 13) {
            $data = $this->getMaratoriumUsers();

            if (isset($_GET['dump'])) {
                echo "<pre>"; print_r($data); echo "</pre>";
                die;
            }

            $sms_template_auto_reject_emergency = $this->sms->get_template($this->sms::AUTO_REJECT_TEMPLATE_EMERGENCY);
            $sms_template_auto_passport_invalid = $this->sms->get_template($this->sms::AUTO_REJECT_TEMPLATE_INVALID_PASSPORT);
            $sms_template_auto_expired = $this->sms->get_template($this->sms::AUTO_REJECT_TEMPLATE_EXPIRED);

            foreach ($data as $user) {
               $user = (object) $user;
               $message  = $resp = null;
               if (intval($user->reason_id) === 5 && (int) $sms_template_auto_reject_emergency->status === 1) {
                   $property_auto_rej = $sms_template_auto_reject_emergency->template . '_' . $user->site_id;
                   $message = $sms_template_auto_reject_emergency->$property_auto_rej;
                   $resp = $this->smssender->send_sms($user->phone_mobile, $message, $user->site_id);
               }
               if (intval($user->reason_id) === 9 && (int) $sms_template_auto_passport_invalid->status === 1) {
                   $property_auto_rej_em = $sms_template_auto_passport_invalid->template . '_' . $user->site_id;
                   $message = $sms_template_auto_passport_invalid->$property_auto_rej_em;
                   $resp = $this->smssender->send_sms($user->phone_mobile, $message, $user->site_id);
               }
               if (intval($user->reason_id) === 36 && (int) $sms_template_auto_expired->status === 1) {
                   $property_auto_rej_exp = $sms_template_auto_expired->template . '_' . $user->site_id;
                   $message = $sms_template_auto_expired->$property_auto_rej_exp;
                   $resp = $this->smssender->send_sms($user->phone_mobile, $message, $user->site_id);
               }

                $this->sms->add_message(
                    [
                        'user_id' => $user->user_id,
                        'order_id' => $user->order_id,
                        'phone' => $user->phone_mobile,
                        'message' => $message,
                        'created' => date('Y-m-d H:i:s'),
                        'send_status' => $resp[1] ?? null,
                        'delivery_status' => '',
                        'send_id' => $resp[0] ?? null,
                        'type' => $this->smssender::TYPE_MARATORIUM,
                    ]
                );
            }
        }
    }

    public function getMaratoriumUsers(): array
    {
        /**
         *  Доп. условия для совершения рассылки:
         * - клиент не должен быть отписан от рекламных рассылок
         * - клиент не должен находится в ЧС
         * - у клиента не должно быть активного договора (статусы заявки, которые исключаем - Одобрено, Выдано)
         */

        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT 
                    u.phone_mobile,
                    o.user_id,
                    o.id as order_id,
                    o.date,
                    o.reason_id as reason_id
                FROM s_orders o 
                    LEFT JOIN s_users u ON u.id = o.user_id
                    LEFT JOIN s_reasons r ON r.id = o.reason_id
                    LEFT JOIN s_blacklist b ON b.user_id = u.id
                WHERE 
                    o.status NOT IN (10, 2)
                    AND o.additional_service = 1 
                    AND b.user_id IS NULL
                    AND o.reason_id IN (5, 9, 36)
                    AND DATE_ADD(o.date, INTERVAL r.maratory DAY) <= '". $currentDate ."' 
                    AND NOT EXISTS (SELECT 1 FROM s_sms_messages s WHERE s.type = ? AND s.user_id = o.user_id AND s.created < '". $currentDate ."')
                    LIMIT 100
                   ";
        $query = $this->db->placehold($sql, $this->smssender::TYPE_MARATORIUM);

        $this->db->query($query);
        $result =  $this->db->results();

        if ($result === false) {
            return [];
        }

        return $result;
    }
}

(new SendSmsMoratoriumCron())->run();