<?php
error_reporting(0);
ini_set('display_errors', 'off');

header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();

class Send1c extends Simpla
{
    const EXPECTED_TOKEN = "RMUjJ3RWHE17gPX8RwJSPmip1qLBIfLpahd8TWq3GfY0ZMQOZMZlqeWim2f3KAsy";
    public function run()
    {
        $token = $this->request->get('token', 'string');
        if (!$this->isValidToken($token)) {
            $this->response->json_output(["status" => "error", "message" => "Invalid token."]);
            return;
        }
        $start = $this->request->get('start_date', 'string');
        $end = $this->request->get('end_date', 'string');
        if (empty($start) || empty($end)) {
            $this->response->json_output(["status" => "error","message" => "Both start and end dates are required."]);
            return;
        }
        elseif (!$this->isValidDateFormat($start) || !$this->isValidDateFormat($end)){
            $this->response->json_output(["status" => "error", "message" => "Both start and end dates must be in the format Y-m-d."]);
            return;
        }
        $orders = $this->getOrderPdn($start,$end);
        $this->response->json_output(["status" => "success","orders" => $orders]);

    }

    private function getOrderPdn($start,$end)
    {
        $query = $this->db->placehold("
            SELECT order_uid,pdn_nkbi_loan 
            FROM __orders AS o               
            WHERE confirm_date BETWEEN ? AND ?
            AND pdn_nkbi_loan is not null 
            AND pdn_nkbi_loan != 0
        ", $start,$end);

        $this->db->query($query);
        
        return $this->db->results();
    }
    private function isValidToken($token): bool
    {
        return hash_equals(self::EXPECTED_TOKEN, $token);
    }
    private function isValidDateFormat($date)
    {
        $format = 'Y-m-d';
        $dateTime = \DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
    }

}

(new Send1c())->run();
