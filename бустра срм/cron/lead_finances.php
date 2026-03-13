<?php
//error_reporting(-1);
//ini_set('display_errors', 'On');
//ini_set('max_execution_time', '600');
//
//require_once dirname(__FILE__).'/../api/Simpla.php';
//
//class LeadFinancesCron extends Simpla
//{
//    //Время в минутах от отказа
//    protected const WAIT_BEFORE_SEND = 30;
//    protected const LINK  = 'https://api.apiprofit.ru/v1/lead/add';
//    protected const TOKEN = 'fd1283a472e64a0087cdbfb9eb024435';
//
//    public function __construct()
//    {
//    	parent::__construct();
//    }
//
//    public function run()
//    {
//        $query = $this->db->placehold("SELECT * FROM __reject_queue
//                                       WHERE DATE_ADD(NOW(), INTERVAL -? MINUTE) > reject_date
//                                        AND response IS NULL
//                                        LIMIT 300",
//                                      static::WAIT_BEFORE_SEND);
//        $this->db->query($query);
//        if ($results = $this->db->results()) {
//            foreach ($results as $result) {
//                if($result->order_id) {
//                    $mutex_key = "GET_LOCK('LeadFinancesCron_{$result->order_id}', 0)";
//                    $this->db->query("SELECT $mutex_key");
//                    $result_mutex = $this->db->result();
//                    if(isset($result_mutex->{$mutex_key})
//                        && $result_mutex->{$mutex_key}) {
//                            $response = $this->sendRequest($result->order_id);
//                            if(!empty($response)) {
//                                $query = $this->db->placehold("UPDATE __reject_queue SET response=? WHERE id = ?", json_encode($response, JSON_UNESCAPED_UNICODE), $result->id);
//                                $this->db->query($query);
//                                $this->db->query("DO RELEASE_LOCK('LeadFinancesCron_{$result->order_id}')");
//                            }
//                    }
//                }
//            }
//        }
//    }
//
//    private function sendRequest($order_id)
//    {
//        $order = $this->orders->get_order($order_id);
//        $passport_serial = str_replace(array(' ', '-'), '', $order->passport_serial);
//        $serial = substr($passport_serial, 0, 4);
//        $number = substr($passport_serial, 4, 6);
//        $params = [
//            'token' => self::TOKEN,
//            'first_name' => $order->firstname,
//            'middle_name' => $order->patronymic ?? '',
//            'last_name' => $order->lastname,
//            'phone' => $order->phone_mobile,
//            'email' => $order->email,
//            'birthday' => date('Y-m-d', strtotime($order->birth)),
//            'type' => 1,
//            'policy_accept' => 1,
//            'mailings_accept' => 1,
//            'city_fact' => $order->Regcity,
//            'region_fact' => $order->Regregion,
//            'series_passport' => $serial,
//            'number_passport' => $number,
//            'date_issue_passport' => date('Y-m-d', strtotime($order->passport_date)),
//            'issued_by_passport' => $order->passport_issued,
//            'channel_id' => 1,
//            'channel_name' => 'otkaz'
//        ];
//
//        return $this->curl($params);
//    }
//
//    public function curl($params): array
//    {
//        $curl = curl_init();
//
//        curl_setopt_array($curl, [
//           CURLOPT_URL => self::LINK,
//           CURLOPT_RETURNTRANSFER => true,
//           CURLOPT_POST => true,
//           CURLOPT_POSTFIELDS => http_build_query($params)
//        ]);
//
//        $response = json_decode(curl_exec($curl), true);
//        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//        curl_close($curl);
//
//        $message = json_decode($response['message'], true);
//
//        $response_data = [
//            'code' => $statusCode,
//            'status' => $response['status'],
//            'message' => $this->decode($message !== null ? $message : $response['message']),
//        ];
//
//        $this->logging('add_reject_queue', self::LINK, $params, $response_data, 'lead_finances.txt');
//        return $response_data;
//    }
//
//    public function response($response)
//    {
//        return $this->decode($response);
//    }
//
//    private function decode($string)
//    {
//        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
//            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
//        }, $string);
//    }
//}
//
//$cron = new LeadFinancesCron();
//$cron->run();
