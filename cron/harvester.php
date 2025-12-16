<?php
//error_reporting(-1);
//ini_set('display_errors', 'On');
//
//chdir(dirname(__FILE__).'/../');
//require_once 'api/Simpla.php';
//
//class Harvester extends Simpla
//{
//    const URL = 'https://harvester-project.com/api/register/';
//    const API_KEY = '350pi2ghvnf2fdas';
//
//    const LIMIT_PER_DAY = 100000;
//
//    public function run()
//    {
//        $allOrdersPerDay = $this->orders->get_orders([
//            'limit' => 1000000,
//            'status' => $this->orders::STATUS_REJECTED,
//            'modified_since' => date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00:00'))),
//            'modified_to' => date('Y-m-d H:i:s', strtotime(date('Y-m-d 23:59:59'))),
//        ]);
//
//        $sentPerDay = $this->order_data->countByKeyAndOrder(array_keys($allOrdersPerDay), 'harvester_sent');
//        if (!empty($sentPerDay) && $sentPerDay >= static::LIMIT_PER_DAY) {
//            return false;
//        }
//
//        $allOrdersPerHour = $this->orders->get_orders([
//            'limit' => 1000000,
//            'status' => $this->orders::STATUS_REJECTED,
//            'modified_since' => date('Y-m-d H:i:s', strtotime('-85 minutes')),
//            'modified_to' => date('Y-m-d H:i:s', strtotime('-75 minutes')),
//        ]);
//
//        if ($allOrdersPerHour) {
//            $data = [];
//            $orderIds = [];
//            foreach ($allOrdersPerHour as $order) {
//                $sent = $this->order_data->get($order->id, 'harvester_sent');
//                if ((!empty($sent) && is_object($sent) && $sent->value == 1) || empty($order->user_id)) {
//                    continue;
//                }
//
//                if ($user = $this->users->get_user_by_id((int) $order->user_id)) {
//                    if (!empty($user->phone_mobile)) {
//                        $orderIds[] = $order->id;
//                        $data[] = [
//                            'first_name' => $user->firstname,
//                            'last_name' => $user->lastname,
//                            'middle_name' => $user->patronymic,
//                            'phone' => $user->phone_mobile,
//                            'email' => $user->email,
//                        ];
//                    }
//                }
//            }
//
//            $this->send($data, $orderIds);
//        }
//    }
//
//    private function send($body, $orderIds)
//    {
//        $ch = curl_init(static::URL);
//        curl_setopt_array($ch, array(
//            CURLOPT_POST => TRUE,
//            CURLOPT_RETURNTRANSFER => TRUE,
//            CURLOPT_HEADER => TRUE,
//            CURLOPT_HTTPHEADER => [
//                'Content-Type: application/json',
//                'Api-Provider-Key-Name: ' . static::API_KEY,
//            ],
//            CURLOPT_POSTFIELDS => json_encode($body),
//            CURLOPT_SSL_VERIFYHOST => FALSE,
//            CURLOPT_SSL_VERIFYPEER => FALSE
//        ));
//
//        $response = curl_exec($ch);
//        if ($response === FALSE){
//            die(curl_error($ch));
//        }
//
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        curl_close($ch);
//
//        if ($httpCode == 201) {
//            foreach ($orderIds as $orderId) {
//                $this->order_data->set($orderId, 'harvester_sent', 1);
//            }
//        }
//
//        $this->logging('Harvester', 'cron/harvester.php', $body, $response, 'harvester-cron.log');
//    }
//}
//
//(new Harvester())->run();