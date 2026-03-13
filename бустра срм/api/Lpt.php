<?php

require_once( __DIR__ . '/../api/Simpla.php');

use LPTracker\LPTracker;


class Lpt extends Simpla{

    private $lpt_login;
    private $lpt_password;
    const PROLONGATION = 1582208;//оплата
    const CONTRACT_IS_CLOSED = 1608852;//'Договор закрыт'
    const GOING_OUT = 1608851; //"Выход за 1-3"
    const PROLONGATION_NAME = "Оплата";
    const CONTRACT_IS_CLOSED_NAME = 'Договор закрыт';
    const GOING_OUT_NAME = "Выход за 1-3";

    public function __construct()
    {
        parent::__construct();

        $this->lpt_login = isset($this->settings->apikeys['lpt']['login']) ? $this->settings->apikeys['lpt']['login'] : 'ooo_bustro@weloverobots.ru';
        $this->lpt_password = isset($this->settings->apikeys['lpt']['password']) ? $this->settings->apikeys['lpt']['password'] : 'weloverobots128';
    }

    public function get_lead_for_lpt($date)
    {
        $border_date = isset($date) ? $date : date('Y-m-d');
        $border_date_1_3 = date('Y-m-d', (time() - 86400*3));
        //$border_date = date('Y-m-d', time() + 86400*5);
        $update_border_date = date('Y-m-d H:i:s', (time() - 21600));

        $query = $this->db->placehold("
            SELECT *
            FROM __user_balance ub 
            WHERE DATE(ub.payment_date) <= ?
            AND DATE(ub.payment_date) >= ?
            AND (ub.ostatok_od > 0 OR ub.ostatok_percents > 0 OR ub.ostatok_peni > 0)
            AND ub.lpt_lead = 0
            AND ub.last_update >= ?
            LIMIT 5
        ", $border_date, $border_date_1_3, $update_border_date);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
        $this->db->query($query);
        if ($user_balances = $this->db->results())
        {
            foreach ($user_balances as &$user_balance)
            {
                $user_balance->restructurisation = unserialize($user_balance->restructurisation);
            }
        }

        return $user_balances;
    }

    public function get_ltp_by_user_balance($user_balance_id) {
        $query = $this->db->placehold("
            SELECT *
    		FROM __lpt lpt 
            WHERE lpt.user_balance_id=? 
            LIMIT 1
        ", $user_balance_id);

        $this->db->query($query);

        $lpt = $this->db->result();

        return $lpt;
    }

    public function get_ltp_collection_by_user_balance($user_balance_id) {
        $query = $this->db->placehold("
            SELECT *
    		FROM __lpt lpt 
            WHERE lpt.user_balance_id=? 
            LIMIT 9
        ", $user_balance_id);

        $this->db->query($query);

        $lpt = $this->db->result();

        return $lpt;
    }

    public function get_lpt_by_lpt_id($lpt_id) {
        $query = $this->db->placehold("
            SELECT *
    		FROM __lpt lpt 
            WHERE lpt.lpt_id=? 
            LIMIT 1
        ", $lpt_id);

        $this->db->query($query);

        $lpt = $this->db->result();

        return $lpt;
    }

    public function get_user_balance_by_id($id)
    {
        $id = intval($id);

        $query = $this->db->placehold("
            SELECT *
    		FROM __user_balance ub 
            WHERE ub.id=? 
            LIMIT 1
        ", $id);

        $this->db->query($query);
        if ($user_balance = $this->db->result())
        {
            $user_balance->restructurisation = unserialize($user_balance->restructurisation);
        }

        if(empty($user_balance))
            return false;

        return $user_balance;
    }

    public function send_lead($id) {
        $api = new LPTracker([
            'login'    => $this->lpt_login,
            'password' => $this->lpt_password,
            'service'  => 'boostra_test'
        ]);

        $user_balance = $this->get_user_balance_by_id($id);

        $user = $this->users->get_user($user_balance->user_id);
        $phone = $user->phone_mobile;

        $name = $user_balance->client . ' | ' . $id;

        $details = [
            [
                'type' => 'phone',
                'data' => $phone
            ]
        ];

        $contactData = [
            'name'       => $name,
        ];

        $contact = $api->createContact(86621, $details, $contactData);

        $leadData = [
            'name' => $name,
            'source' => 'boostra_test'
        ];

        $options = [
            'callback' => true
        ];

        $lead = $api->createLead($contact, $leadData, $options);

        $api->saveLead($lead);

        return $lead;
    }

    public function add_item($item)
    {
        $query = $this->db->placehold("
            INSERT INTO __lpt SET ?%
        ", (array)$item);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_item($lpt_id, $item)
    {
		$query = $this->db->placehold("
            UPDATE __lpt SET ?% WHERE lpt_id = ?
        ", (array)$item, (int)$lpt_id);
        $this->db->query($query);

        return $lpt_id;
    }

    public function change_lpt_status($lpt_id, $step_id) {
        $api = new LPTracker([
            'login'    => $this->lpt_login,
            'password' => $this->lpt_password,
            'service'  => 'boostra_test'
        ]);

        $lead_changed = $api->changeLeadFunnel((int) $lpt_id, (int) $step_id);

        //$api->editLeadCustom((int) $lpt_id, 1706950, "20:09:2021 16:00:18");

        $token = $api->login($this->lpt_login, $this->lpt_password, 'boostra_test');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://direct.lptracker.ru/lead/'.$lpt_id.'/custom/1706950',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS =>'{
            "value": "'.date("d.m.Y H:i").'"
        }',
          CURLOPT_HTTPHEADER => array(
            'token: '.$token->getValue().'',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        //var_dump($response);
        $this->logging(__METHOD__, '', "lpt - {$lpt_id}/step - {$step_id}", $response, 'lpt_change_status.txt');
    }

    public function get_going_out_lpt($limit = 9) {
        //$border_date = '2021-09-11 00:00:00';
        $border_date = date('Y-m-d H:i:s', (time() - 86400*3));

        $query = $this->db->placehold("
            SELECT * FROM __lpt, __user_balance ub 
            WHERE __lpt.user_balance_id = ub.id 
            AND DATE(ub.payment_date) < ?
            AND NOT __lpt.status = 'Выход за 1-3'
            AND NOT __lpt.status = 'Оплата'
            AND NOT __lpt.status = 'Договор закрыт'
            AND NOT ub.zaim_number = 'Нет открытых договоров'
            LIMIT ?
        ", $border_date, $limit);

        $this->db->query($query);
        $lpt_leads = $this->db->results();

        return $lpt_leads;
    }

    public function get_all_lpt_for_work($limit = 9) {
        //$border_date = '2021-09-11 00:00:00';
        $border_date = date('Y-m-d H:i:s', (time() - 86400*3));
        $border_refresh_date = date('Y-m-d H:i:s', (time() - 7200));

        $query = $this->db->placehold("
            SELECT * FROM __lpt, __user_balance ub 
            WHERE __lpt.user_balance_id = ub.id 
            AND DATE(ub.payment_date) >= ?
            AND NOT __lpt.status = 'Выход за 1-3'
            AND NOT __lpt.status = 'Оплата'
            AND NOT __lpt.status = 'Договор закрыт'
            AND DATE(__lpt.updated_at) < ?
            LIMIT ?
        ", $border_date, $border_refresh_date, $limit);

        $this->db->query($query);
        $lpt_leads = $this->db->results();

        return $lpt_leads;
    }

    //public function get_paid_lpt($limit = 10) {
    //    $query = $this->db->placehold("
    //        SELECT * FROM __lpt, __user_balance ub
    //        WHERE __lpt.user_balance_id = ub.id
    //          AND (ub.ostatok_od = 0 AND ub.ostatok_percents = 0 AND ub.ostatok_peni = 0)
    //        AND NOT __lpt.status = 'Выход за 1-3'
    //        AND NOT __lpt.status = 'Нулевой долг'
    //        LIMIT ?
    //    ", $limit);
//
    //    $this->db->query($query);
    //    $lpt_leads = $this->db->results();
//
    //    return $lpt_leads;
    //}

    public function update_all_in_working_lpt($limit = 9) {
        $lpt_collection = $this->get_all_lpt_for_work($limit);

        foreach ($lpt_collection as $lpt) {
            $user = $this->users->get_user((int)$lpt->user_id);

            if (!empty($user->UID))
            {
                $user_balance_1c = $this->soap->get_user_balance_1c($user->UID, $user->site_id);
                $user_balance_1c = $this->import1c->import_user_balance($user->id, $user_balance_1c->return);
            }
        }
    }

    public function update_going_out_lpt_by_user_balance($limit = 9) {
        $lpt_leads = $this->get_going_out_lpt($limit);

        foreach ($lpt_leads as $lpt_lead) {
            $this->change_lpt_status($lpt_lead->lpt_id, self::GOING_OUT);
        }
    }

    public function update_paid_lpt_by_user_balance($limit) {
        $lpt_leads = $this->get_paid_lpt($limit);

        foreach ($lpt_leads as $lpt_lead) {
            $this->change_lpt_status($lpt_lead->lpt_id, 1608852);
        }
    }

    public function check_if_payment_date_changed($id, $user_balance_by_1c) {
        $user_balance = $this->get_user_balance_by_id($id);

        if (strtotime($user_balance->payment_date) == strtotime($user_balance_by_1c->payment_date)) {
            return false;
        } else {
            $this->logging(__METHOD__, 'true', $user_balance->payment_date, $user_balance_by_1c->payment_date, 'lpt_refresh.txt');
            return true;
        }
    }

    public function refresh_lpt_lead($user_balance_id, $user_balance_by_1c) {
        try {
            $lpt = $this->get_ltp_by_user_balance($user_balance_id);
            if ($lpt) {
                if (!in_array($lpt->status, [self::PROLONGATION_NAME, self::CONTRACT_IS_CLOSED_NAME, self::GOING_OUT_NAME])) {
                    if ($user_balance_by_1c->zaim_number == "Нет открытых договоров") {
                        $this->logging(__METHOD__, 'Нет открытых договоров', $user_balance_id, $lpt->lpt_id, 'lpt_refresh.txt');
                        $this->change_lpt_status($lpt->lpt_id, self::CONTRACT_IS_CLOSED);
                    } elseif ($this->check_if_payment_date_changed($user_balance_id, $user_balance_by_1c)) {
                        $this->change_lpt_status($lpt->lpt_id, self::PROLONGATION);
                    }
                }
            }
        } catch (Exception $e) {
            $this->logging(__METHOD__, 'refresh', $user_balance_id, $e->getMessage(), 'lpt_refresh.txt');
        }
    }
}
