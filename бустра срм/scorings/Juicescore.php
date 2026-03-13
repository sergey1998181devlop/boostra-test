<?php

class Juicescore extends Simpla
{
    private const TIMEOUT = 15;
    
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;
    
    private $key = '';
    private $url = 'https://api.juicyscore.com/getscore/';
    private $account_id = 'Boostra_RU';
    
    public function __construct()
    {
    	parent::__construct();

        $this->key = $this->settings->apikeys['juicescore']['api_key'];
    }

    public function run_scoring($scoring_id)
    {
        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            $this->scorings->update_scoring($scoring_id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Проверка перенесена на сторону АксиНБКИ',
                'end_date' => date('Y-m-d H:i:s')
            ]);
            return;

            if ($order = $this->orders->get_order((int)$scoring->order_id)) {
                if (empty($order->juicescore_session_id)) {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не найден идентификатор сессии juicescore'
                    );
                } else {
                    if ($json_result = $this->getscore($order->order_id)) {
                        $result = (array)json_decode($json_result);

                        if (!empty($result['Success'])) {
                            $success = true;
                            $criteriaError = false;
                            $criterias = $this->juicescoreCriteria->getAll();
                            foreach ($criterias as $criteria) {
                                if (isset($result[$criteria->name])) {
                                    $ball = (float)$result[$criteria->name];
                                    if ($ball < $criteria->required_ball) {
                                        $success = false;
                                        break;
                                    }
                                } else {
                                    $criteriaError = true;
                                    $update = [
                                        'status' => $this->scorings::STATUS_ERROR,
                                        'string_result' => "Критерий \"{$criteria->name}\" отсутствует в заявке"
                                    ];
                                    break;
                                }
                            }

                            if (!$criteriaError) {
                                if (empty($criterias)) {
                                    $update = [
                                        'status' => $this->scorings::STATUS_ERROR,
                                        'string_result' => "Список критериев пуст"
                                    ];
                                } else {
                                    $update = [
                                        'status' => $this->scorings::STATUS_COMPLETED,
                                        'body' => serialize($result),
                                        'success' => $success,
                                        'string_result' => $success ? 'Проверка пройдена' : 'Проверка не пройдена',
                                    ];
                                }
                            }
                        } else {
                            $update = array(
                                'status' => $this->scorings::STATUS_ERROR,
                                'string_result' => 'При запросе произошла ошибка',
                                'body' => serialize($result),
                            );
                        }
                    } else {
                        $update = array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'string_result' => 'Не удалось выполнить запрос',
                        );
                    }
                }
            } else {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                );
            }
            
            if (!empty($update)) {
                $update['end_date'] = date('Y-m-d H:i:s');
                $this->scorings->update_scoring($scoring_id, $update);
                if (!empty($scoring->order_id)) {
                    $this->scorings->tryAddScoristaAndAxi($scoring->order_id);
                }
            }
            
            return $update;

        }
    }
    
    public function getscore($order_id)
    {
        if (!($order = $this->orders->get_order((int)$order_id)))
            return false;
        
        $email_expls = explode('@', $order->email);
        $prepare_email = substr(substr($email_expls[0], 0, -1), -20);

        // 188.123.232.17 -> 188.123.232.
        $ip = $order->ip;
        $ipParts = explode('.', $ip);
        if (count($ipParts) == 4)
            $ip = implode('.', array_slice($ipParts, 0, 3)) . '.';

        // Сведения о системе и браузере
        $useragent = $this->order_data->read($order_id, $this->order_data::USERAGENT) ?? '';

        $params = array(
            'account_id' => $this->account_id,
            'client_id' => $order->user_id,
            'session_id' => $order->juicescore_session_id,
            'channel' => 'SITE',
            'time_utc3' => date('d.m.Y H:i:s', strtotime($order->date)),
            'version' => 15,
            'referrer' => '',
            'tenor' => $order->period,
            'time_local' => '',
            'ip' => $ip,
            'useragent' => $useragent,
            'ph_country' => '7',
            'phone' => substr($order->phone_mobile, 1, 6),
            'mail' => empty($prepare_email) ? '' : $prepare_email,
            'application_id' => $order->order_id,
            'time_zone' => '',
            'amount' => $order->amount,
            'mac_address' => '',
            'deviceid' => '',
            'zip_billing' => '',
            'country_code_billing' => 'RU',
            'zip_shipping' => '',
            'country_code_shipping' => 'RU',
            'card_number' => '',
            'card_expiration_date' => '',
            'response_content_type' => 'json',
        );

        $url = $this->url.'?'.http_build_query($params);
        
        $headers = array(
            'session: '.$this->key
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, static::TIMEOUT);

        $res = curl_exec($ch);
        curl_close($ch);

        $this->logging(__METHOD__, $url, $params, (array)$res, 'juiceScore.txt');

        return $res;
    }
}