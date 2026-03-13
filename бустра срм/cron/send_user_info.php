<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * Class SendUserInfo
 * Класс служит для отправки данных пользователя в отказных заявках
 */

class SendUserInfo extends Simpla
{
    public const URL = 'https://admin.ryabina.org/api/';
    public const API_KEY = '1c3e3d2e0e49';
    public const PARTNER_KEY = '2ubUIl0OcEkK';

    public function __construct()
    {
        parent::__construct();
        $this->run();
        echo 'Данные о пользователях в отказанных заявках отправлены';
    }

    private function run()
    {
        $canceled_orders = $this->orders->getCanceledOrdersUserId();
        if(!empty($canceled_orders)) {
            foreach ($canceled_orders as $canceled_order) {
                $user_info = $this->users->get_user((int)$canceled_order->user_id);

                $fields = (Object)[
                    'Clients' =>
                        [
                            'phone' =>  (int)$user_info->phone_mobile,
                            'name' => trim($user_info->firstname),
                            'last_name' => trim($user_info->lastname),
                            'middle_name' => trim($user_info->patronymic),
                        ],
                ];

                $url = self::URL . 'register';

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 6,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_HTTPHEADER => [
                        "API-Key: " . self::API_KEY,
                        "partner-key: " . self::PARTNER_KEY,
                        "Content-Type: application/x-www-form-urlencoded",
                    ],
                    CURLOPT_POSTFIELDS => http_build_query($fields),
                ]);

                $res = curl_exec($ch);
                curl_close($ch);

                $response = json_decode($res, true);

                //if ((int)$response['status'] === 200) {
                    $this->orders->update_order($canceled_order->id, ['send_user_info_date' => date('Y-m-d H:i:s')]);
                //}

                $this->logging( __METHOD__, $url, $fields, $response, 'ryabina_request.txt');
            }
        }
    }
}

new SendUserInfo();
