<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__FILE__).'/../api/Simpla.php';

class sendDeclinesToLeadgens extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
        $leadgens = array_filter(explode(',', $this->settings->send_declines_leadgens_list), fn($leadgen) => method_exists($this, "{$leadgen}Send"));
        if(empty($leadgens)) {
            return;
        }
        $this->db->query("SET @end_hour := NOW() - INTERVAL 14 DAY");
        $this->db->query("SET @start_hour := @end_hour - INTERVAL 1 HOUR");
        $this->db->query("SELECT DISTINCT o.user_id
                            FROM s_orders o
                            LEFT JOIN s_orders o1
                                ON o1.user_id = o.user_id
                                AND o1.id > o.id
                                AND o1.`status` NOT IN (3)
                            WHERE
                                o.reject_date >= @start_hour
                                AND o.reject_date < @end_hour
                                AND o1.id IS NULL");
        $user_ids = $this->db->results('user_id');

        foreach($leadgens as $leadgen) {
            $this->{"{$leadgen}Send"}($user_ids);
        }
    }

    public function apiprofitSend($user_ids)
    {
        foreach($user_ids as $user_id) {
            $user = $this->users->get_user($user_id);
            $data  = http_build_query([
                'token' => $this->config->decline_leadgens['apiprofit_token'],
                'phone' => "+{$user->phone_mobile}",
                'type' => 1,
                'policy_accept' => 1,
                'mailings_accept' => 1,
                'channel_id' => $this->config->decline_leadgens['apiprofit_channel_id'],
                'channel_name' => $this->config->decline_leadgens['apiprofit_channel_name'],
                'first_name' => $user->firstname,
                'last_name' => $user->lastname,
                'middle_name' => $user->patronymic,
                'region_fact' => $user->Faktregion,
                'city_fact' => $user->Faktcity,
            ]);

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.apiprofit.ru/v1/lead/add',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_TIMEOUT => 5,
            ]);

            $response = json_decode(curl_exec($curl), true);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if($statusCode != 200) {
                $this->logging(__METHOD__, 'https://api.apiprofit.ru/v1/lead/add', $data, $response, 'apiprofitSend_errors.txt');
            }

            curl_close($curl);
        }
    }
}

set_time_limit(3000);
$cron = new sendDeclinesToLeadgens();
$cron->run();
