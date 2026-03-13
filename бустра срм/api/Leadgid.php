<?php

require_once( __DIR__ . '/../api/Simpla.php');
//include('addons/sms_new.php');

use api\YaCC;

class Leadgid extends Simpla
{
    private $leadgid_token;
    private $switched_sms = 'On';
    private $switched_api = 'On';
    private $switched = 'On';

    private $sources = [
        'acado' => 1,
        'akvariusmkk' => 2,
        'alliance' => 3,
        'Android' => 4,
        'Android App' => 5,
        'bankiru' => 6,
        'beegl' => 7,
        'beeline' => 8,
        'bgl' => 9,
        'Boostra' => 10,
        'c2m' => 11,
        'cdoctor' => 12,
        'cf' => 13,
        'cityads' => 14,
        'crm_auto_approve' => 15,
        'divide_order' => 16,
        'e_mail' => 17,
        'finanso' => 18,
        'finrozn_redir' => 19,
        'fr' => 20,
        'guruleads' => 21,
        'kosmos' => 22,
        'leadcraft' => 23,
        'leadcraft_click' => 24,
        'leadfin' => 25,
        'leadgid' => 26,
        'LW' => 27,
        'LW2' => 27,
        'leadstech' => 28,
        'leadtarget' => 29,
        'mvp' => 30,
        'partnerdecline' => 31,
        'rafinad' => 32,
        'redirect_acado' => 33,
        'showcase' => 34,
        'sms' => 35,
        'sms_23' => 36,
        'sravni' => 37,
        'telegram_bot' => 38,
        'unicom24' => 39,
        'vibery' => 40,
        'vk' => 41,
        'vse-mikrozaimy' => 42,
        'whatsap' => 43,
        'whatsapp' => 44,
        'yafavorites' => 45,
        'zaimer' => 46,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->leadgid_token = isset($this->settings->apikeys['leadgid']['token']) ? $this->settings->apikeys['leadgid']['token'] : 'ae679006ae238f61c00a2d7dc5446cf22bb1bd5b';
    }

    public function add_lead_to_leadgid($order, $user) {
        if ($this->switched_api == 'Off') {
            $firstname = 'ТЕСТ';
        } else {
            $firstname = $user->firstname;
        }

        if ($user->passport_serial) {
            $passport = str_replace(array('-', ' '), '', $user->passport_serial);
            $passport_serial = substr($passport, 0, 4);
            $passport_number = substr($passport, 4, 6);
        } else {
            $passport = '';
            $passport_serial = '';
            $passport_number = '';
        }

        if ($user->passport_date) {
            $issued_date = date('Y-m-d', strtotime($user->passport_date));
        } else {
            $issued_date = '';
        }

        $birth = date("Y-m-d", strtotime($user->birth));

        $scoreBall = $order->scorista_ball ?? 0;

        $sourceNumber = $this->getSourceNumber($order->utm_source);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ldr.13evl.com/api/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
    "method": "leads.add",
    "params": {
        "phone": "'.$user->phone_mobile.'",
        "api_type_id": 1,
        "data": {
            "site": "boostra.ru",
            "client_ip": "'.$order->ip.'",
            "user_id": "'.$order->user_id.'",
            "product": "PDL",
            "cell_phone": "'.$user->phone_mobile.'",
            "first_name": "'.$firstname.'",
            "last_name": "'.$user->lastname.'",
            "middle_name": "'.$user->patronymic.'",
            "birth_date": "'.$birth.'",
            "agree_with_terms": "Есть согласие",
            "city_fact": "'.$user->Faktcity.'",
            "passport_code": "'.$user->subdivision_code.'",
            "passport_seria": "'.$passport_serial.'",
            "passport_num": "'.$passport_number.'",
            "passport_issued": "'.$user->passport_issued.'",
            "passport_date": "'.$issued_date.'",
            "score_ball": "'.$scoreBall.'",
            "source_number": "'.$sourceNumber.'"
        }
    }
}',
            CURLOPT_HTTPHEADER => array(
                'content-type: application/json',
                'token: '.$this->leadgid_token.''
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $this->logging(__METHOD__, '', [(array)$order, 'json' => '{
            "method": "leads.add",
            "params": {
                "phone": "'.$user->phone_mobile.'",
                "api_type_id": 1,
                "data": {
                    "site": "boostra.ru",
                    "client_ip": "'.$order->ip.'",
                    "user_id": "'.$order->user_id.'",
                    "product": "PDL",
                    "cell_phone": "'.$user->phone_mobile.'",
                    "first_name": "'.$firstname.'",
                    "last_name": "'.$user->lastname.'",
                    "middle_name": "'.$user->patronymic.'",
                    "birth_date": "'.$birth.'",
                    "agree_with_terms": "Есть согласие",
                    "city_fact": "'.$user->Faktcity.'",
                    "passport_code": "'.$user->subdivision_code.'",
                    "passport_seria": "'.$passport_serial.'",
                    "passport_num": "'.$passport_number.'",
                    "passport_issued": "'.$user->passport_issued.'",
                    "passport_date": "'.$issued_date.'",
                    "score_ball": "'.$scoreBall.'",
                    "source_number": "'.$sourceNumber.'"
                }
            }
        }'], (array)$response, 'leadgid_api.txt');

        return $response;
    }

    private function getSourceNumber($sourceName) {
        return isset($this->sources[$sourceName]) ? $this->sources[$sourceName] : null;
    }

    public function send_leadgid_sms($phone, $message, $site_id) {
        $return = $this->smssender->send_sms($phone, $message, $site_id);
        //$return = send_sms_new($phone, $message, $originator='Boostra.ru', $rus=0);
        $this->logging(__METHOD__, 'send_sms', $phone, (array)$return, 'leadgid_sms.txt');
    }

    public function reject_actions($order_id) {
        if ($this->switched != 'On' || $this->switched_sms != "On")
            return;

        $has_reject_actions = $this->order_data->read($order_id, $this->order_data::HAS_REJECT_ACTIONS);
        if ($has_reject_actions) // Предотвращает повторную отправку смс по одной и той же заявке
            return;

        $order = $this->orders->get_order($order_id);
        $user = $this->users->get_user($order->user_id);

        if ($order->have_close_credits == 0) {
            $bonon_enabled = $this->settings->bonon_enabled;
            $is_rejected_nk = $this->user_data->read($order->user_id, 'is_rejected_nk');
            if (!empty($bonon_enabled) && (!isset($is_rejected_nk) || $is_rejected_nk == 1)) {
                // Не отправляем отказные смс если клиент продан в бонон или ждёт решение по продаже
                return;
            }
        }

        // У клиента самозапрет на выдачу займов, не отправляем стандартные СМС
        if (in_array($order->reason_id, [
                $this->reasons::REASON_INN_NOT_FOUND,
                $this->reasons::REASON_SELF_DEC
            ])) {
            $this->order_data->set($order_id, $this->order_data::HAS_REJECT_ACTIONS, 1);

            if ($order->reason_id == $this->reasons::REASON_SELF_DEC) {
                $this->send_leadgid_sms($user->phone_mobile, 'Ваша заявка отклонена из-за самозапрета в кредитной истории', $user->site_id);
            }

            return;
        }

        // Переключение организации - не отправляем СМС
        if ($order->reason_id == $this->reasons::REASON_SWITCH_ORGANIZATION) {
            $this->order_data->set($order_id, $this->order_data::HAS_REJECT_ACTIONS, 1);
            return;
        }

        $template = $this->sms->get_template($this->sms::AUTO_REJECT_TEMPLATE_NOW, $user->site_id);
        if (!empty($template->status)) {
            $message = strtr($template->template, [
                '{{firstname}}' => $order->firstname,
                '{{phone_mobile}}' => $order->phone_mobile,
            ]);

            //$cc = new YaCC();
            //$message = $cc->replaceAllUrls($message);

            $this->logging(__METHOD__, 'send_sms', $user->phone_mobile, $message, 'leadgid_api.txt');
            $this->send_leadgid_sms($user->phone_mobile, $message, $user->site_id);
        }

        $template_second = $this->sms->get_template($this->sms::AUTO_REJECT_TEMPLATE_SECOND, $user->site_id);
        if (!empty($template_second->status)) {
            $message_second = strtr($template_second->template, [
                '{{firstname}}' => $order->firstname,
                '{{amount}}' => $order->amount,
            ]);

            $this->save_leadgid_sms([
                'phone' => $user->phone_mobile,
                'message' => $message_second,
                'number_of' => 1,
                'firstname' => $user->firstname
            ]);
        }

        $this->order_data->set($order_id, $this->order_data::HAS_REJECT_ACTIONS, 1);
    }


    public function send_postback_to_mvp($order) {
        if ($order->utm_source == 'mvp') {
            $link_lead = 'http://109.68.214.82/b08845a/postback?subid='.$order->click_hash.'&status=rejected';

            $ch = curl_init($link_lead);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            $res = curl_exec($ch);
            curl_close($ch);

            $this->logging(__METHOD__, 'rejected', $link_lead, $res, 'mvp_leads.txt');

            //
            $link_lead = 'https://tracker.mvpgroup.ru/d595109/postback?subid='.$order->click_hash.'&status=rejected';

            $ch = curl_init($link_lead);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            $res = curl_exec($ch);
            curl_close($ch);

            $this->logging(__METHOD__, 'rejected', $link_lead, $res, 'mvp_leads_mvpgroup.txt');
        } elseif ($order->utm_source == 'leadcraft') {
            $this->post_back->sendReject($order);
        } elseif ($order->utm_source == 'leadstech') {
            $link_lead = 'https://offers.leads.tech/add-conversion/?click_id='.$order->click_hash.'&goal_id=3&status=2&transaction_id='.$order->id_1c;

            $ch = curl_init($link_lead);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $res = curl_exec($ch);
            curl_close($ch);

            $this->logging(__METHOD__, 'cancelled', $link_lead, $res, 'leadstech.txt');
        }
    }

    public function save_leadgid_sms($item)
    {
        $item = (array)$item;

        if (empty($item['created_at'])) {
            $item['created_at'] = date('Y-m-d H:i:s');
            $item['updated_at'] = date('Y-m-d H:i:s');
        }
        // @todo на будущее надо сделать связку с order_id
        $query = $this->db->placehold("
            INSERT INTO __leadgid_sms_log SET ?%
        ", $item);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_leadgid_sms($id, $item)
    {
        $item['updated_at'] = date('Y-m-d H:i:s');

        $query = $this->db->placehold("
            UPDATE __leadgid_sms_log SET ?% WHERE id = ?
        ", (array)$item, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function get_queue_for_sending_sms($limit = 9)
    {
        $query = $this->db->placehold("
            SELECT * FROM __leadgid_sms_log WHERE number_of < 5 AND created_at > '2024-04-27 15:00' AND updated_at < NOW() - INTERVAL 14 MINUTE LIMIT ?
        ", (int)$limit);
        $this->db->query($query);
        $result = $this->db->results();

        return $result;
    }
}