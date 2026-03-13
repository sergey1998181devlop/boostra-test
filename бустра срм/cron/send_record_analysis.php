<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

class SendRecordAnalysis extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        $url = $this->config->back_url . '/app/comments/record-analysis/send';

        $dateFrom = date('Y-m-d H:i:s', strtotime('-1 day'));
        $dateTo = date('Y-m-d H:i:s');

        $postData = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];

        $token = $this->getToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            var_dump("Curl error: " . $error);
            logger('mango_cron')->error("Curl error: " . $error);
        }

        curl_close($ch);

        $data = json_decode($response, true);

        print_r($data);
    }

    private function getToken(): string
    {
        $sql = "SELECT token FROM application_tokens WHERE name = 'Obuchat'";

        $this->db->query($sql);
        return $this->db->result('token');
    }
}

$cron = new SendRecordAnalysis();
$cron->run();
