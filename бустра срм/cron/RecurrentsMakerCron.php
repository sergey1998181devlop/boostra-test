<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 600);

require_once dirname(__FILE__).'/../api/Simpla.php';

class RecurrentsMakerScript extends Simpla
{
    // количество обрабатываемых строк
    private $itteration_count = 40;
    private $percents_scheme = [2, 10, 10, 10, 10, 10, 10, 10, 10, 18];
    private $block_card_hours = 72;
    private $block_messages = [
        'Invalid transaction. Decline by Issuer.',
        'Transaction declined by Issuer',
        'Decline by Issuer.',
        'Acquirer limit checkup failure',
        'Card expired',
        'Antifraud checkup failure',
        'Merchant usage limit checkup failure or card number unknown',
    ];
    
    public function __construct()
    {
        $this->run();
    }
    
    private function get_items()
    {
        $this->db->query("
            SELECT * FROM s_recurrents
            WHERE status = 0
            LIMIT ?
        ", $this->itteration_count);
        $results = [];
        foreach ($this->db->results() as $r)
            $results[$r->id] = $r;
        
        if (!empty($results))
        {
            $this->db->query("
                UPDATE s_recurrents SET status = 1 WHERE id IN (?@)
            ", array_keys($results));
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';
        
        return $results;
    }
    
    private function run()
    {
        if ($items = $this->get_items())
        {
            foreach ($items as $item)
            {
                // находим order
                if ($order = $this->get_order($item)) {

                    $user = $this->users->get_user((int)$order->user_id);
                } else {
                    $user_id = $this->users->get_uid_user_id($item->client_uid);
                    $user = $this->users->get_user($user_id);
                }
                if (!empty($user))
                {
                    if ($balance = $this->get_balance($order, $item, $user))
                    {
                        $this->recurrents->update_reccurent($item->id,['od' => $balance['ОстатокОД'],'percents' => $balance['ОстатокПроцентов']]);
                        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($balance);echo '</pre><hr />';
                        $current_organization_id = $this->organizations->get_organization_id_by_inn($balance['ИННТекущейОрганизации']);
                        if ($cards = $this->get_cards($order, $current_organization_id, $user))
                        {
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($cards);echo '</pre><hr />';
                            $rest_amount = floatval($balance['ОстатокПроцентов'] + $balance['ОстатокОД']);
                            if ($item->payment_type == 'prolongation') {
                                $rest_amount = floatval($balance['ОстатокПроцентов']);
                            }
                            if ($rest_amount < 1)
                                $this->update_item($item->id, [
                                    'status' => 6,
                                    'string_result' => 'Остаток меньше 1 руб'
                                ]);
                            elseif ($rest_amount > 100)
                                $this->make_cascade_recurrent($item, $order, $balance, $cards, $current_organization_id, $user);
                            else
                                $this->make_single_recurrent($item, $order, $balance, $cards, $current_organization_id, $user);

                        }
                        else
                        {
                            $this->update_item($item->id, [
                                'status' => 7,
                                'string_result' => 'Нет доступных карт для списания'
                            ]);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Нет доступных карт для списания');echo '</pre><hr />';
                        }            
                    }
                    else
                    {
                        $this->update_item($item->id, [
                            'status' => 8,
                            'string_result' => 'Баланс не найден'
                        ]);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Баланс не найден');echo '</pre><hr />';
                    }            
                } else {
                    $this->update_item($item->id, [
                        'status' => 9,
                        'string_result' => 'Клиент не найден'
                    ]);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Клиент не найден');echo '</pre><hr />';
                }
            }
        }
    }
    
    private function make_single_recurrent($item, $order, $balance, $cards, $current_organization_id, $user)
    {        
        $rest_percents = 100;
        $rest_amount = floatval($balance['ОстатокПроцентов'] + $balance['ОстатокОД']);
        $total_amount = floatval($balance['ОстатокПроцентов'] + $balance['ОстатокОД']);
        $prolongation = 0;
        if ($item->payment_type == 'prolongation') {
            $rest_amount = floatval($balance['ОстатокПроцентов']);
            $total_amount = floatval($balance['ОстатокПроцентов']);
            $prolongation = 1;
        }
        foreach ($cards as $card)
        {

            $recurrent_params = [
                'user_id' => $user->id,
                'description' => 'Списание задолженности по договору займа '.$item->number,
                'order_id' => empty($order->order_id) ? 0 : $order->order_id,
                'number' => $item->number,
                'card_id' => $card->id,
                'card_token' => $card->token,
                'card_pan' => $card->pan,
                'amount' => 0,
                'percent' => 0,
                'recurrent_id' => $item->id,
                'organization_id' => $current_organization_id,
                'prolongation' => $prolongation
            ];
            
            if ($rest_percents > 0)
            {
                $recurrent_params['percent'] = min($rest_percents, 100);
                $recurrent_params['amount'] = $total_amount;
                
                $rec100 = $this->recurent($recurrent_params);
                if ($rec100 == 'APPROVED') // success
                {
                    $item->getted_percents += $recurrent_params['percent'];
                    $item->getted_amount += $recurrent_params['amount'];

                    $this->update_item($item->id, [
                        'getted_percents' => $item->getted_percents,
                        'getted_amount' => $item->getted_amount
                    ]);
                    
                    $rest_percents -= $recurrent_params['percent'];
                    $rest_amount -= $recurrent_params['amount'];
                    
                }
                elseif ($rec100 == 'TIMEOUT') 
                {
                    return null;
                }
                else
                {
                    continue;
                }
            }
        }
    }
    
    private function make_cascade_recurrent($item, $order, $balance, $cards, $current_organization_id, $user)
    {
        $rest_percents = 100;
        $rest_amount = floatval($balance['ОстатокПроцентов'] + $balance['ОстатокОД']);
        $total_amount = floatval($balance['ОстатокПроцентов'] + $balance['ОстатокОД']);
        $prolongatin = 0;
        if ($item->payment_type == 'prolongation') {
            $rest_amount = floatval($balance['ОстатокПроцентов']);
            $total_amount = floatval($balance['ОстатокПроцентов']);
            $prolongatin = 1;
        }
        $isDecimal = true;
        foreach ($cards as $card)
        {
            $recurrent_params = [
                'user_id' => $user->id,
                'description' => 'Списание задолженности по договору займа '.$item->number,
                'order_id' => empty($order->order_id) ? 0 : $order->order_id,
                'number' => $item->number,
                'card_id' => $card->id,
                'card_token' => $card->token,
                'card_pan' => $card->pan,
                'amount' => 0,
                'percent' => 0,
                'recurrent_id' => $item->id,
                'organization_id' => $current_organization_id,
                'prolongation' => $prolongatin
            ];
            
            foreach ($this->percents_scheme as $key => $percent) {
                
                if ($rest_percents > 0) {
                    $recurrent_params['percent'] = min($rest_percents, $percent);
                    $recurrent_params['amount'] = round($total_amount * $recurrent_params['percent'] / 100);

                    if ($isDecimal) {
                        $decimal_part =  round(fmod($total_amount, 1),2);
                        $recurrent_params['amount'] = round($total_amount * $recurrent_params['percent'] / 100) + $decimal_part;
                    }
                    if ($key == 9) {
                        $recurrent_params['amount'] = $total_amount - $item->getted_amount;
                    }
                    $rec = $this->recurent($recurrent_params);
                    if ($rec == 'APPROVED') // success
                    {
                        $isDecimal = false;
                        $item->getted_percents += $recurrent_params['percent'];
                        $item->getted_amount += $recurrent_params['amount'];
    
                        $this->update_item($item->id, [
                            'getted_percents' => $item->getted_percents,
                            'getted_amount' => $item->getted_amount
                        ]);
                        
                        $rest_percents -= $recurrent_params['percent'];
                        $rest_amount -= $recurrent_params['amount'];
                        
                        sleep(1);
                    }
                    elseif ($rec == 'TIMEOUT') 
                    {
                        return null;
                    }
                }
            }
        }
    }
    
    private function update_item($item_id, $item)
    {
        $query = $this->db->placehold("
            UPDATE s_recurrents 
            SET ?% WHERE id = ?
        ", (array)$item, (int)$item_id);
        $this->db->query($query);
    }
    
    private function get_balance($order, $item, $user)
    {
        if (!empty($order->user_uid)) {
            $user_uid = $order->user_uid;
        } else if (!empty($user->UID)) {
            $user_uid = $user->UID;
        }
        
        if (empty($user_uid)) {
            return NULL;
        }

        $balances = $this->soap1c->get_user_balances_array_1c($user_uid, $user->site_id);
        foreach ($balances as $b)
        {
            if ($b['НомерЗайма'] == $item->number){
                if (($b['ОстатокПроцентов'] > 0 || $b['ОстатокОД'] > 0)) {
                    if (strtotime($b['ПланДата']) <= strtotime(date('Y-m-d'))) {
                        return $b;
                    }
                }
            }
        }
    }
    
    private function get_order($item)
    {        
        if (strpos($item->number, 'Б23') !== false || strpos($item->number, 'Б24') !== false || strpos($item->number, 'A24') !== false)
        {
            $order_id = str_replace(['Б23-', 'Б24-', 'A24-'], '', $item->number);
            
            if ($order = $this->orders->get_order($order_id)) {
                return $order;
            }
        }
        else
        {
            $this->db->query("
                SELECT * FROM s_user_balance
                WHERE zaim_number = ?
            ", $item->number);
            if ($balance = $this->db->result()) {
                $this->db->query("
                    SELECT id FROM s_orders
                    WHERE 1c_id = ? AND user_id = ?
                ", $balance->zayavka, $balance->user_id);
                if ($order_id = $this->db->result('id')) {
                    if (!empty($order_id)) {
                        $order = $this->orders->get_order($order_id);
                        return $order;                                            
                    }
                }
                    
            }
        }
    }
    
    private function get_cards($order, $current_organization_id, $user)
    {
        $pan_exceptions = [
            '555957******5131', 
            '553691******2115',
            '220220******6751',
            '553691******0449',
            '427654******2388',
            '553691******9177',
        ];
        $cards = [];
        foreach ($this->best2pay->get_cards(['user_id'=>$user->id]) as $c) {
            if (empty($c->deleted) && !empty($c->autodebit) && !in_array($c->pan, $pan_exceptions)) {
                if (empty($card->next_recurrent_date) || time() > strtotime($card->next_recurrent_date)) {
                    $cards[$c->id] = $c;
                }
            }
        }
        
        if (!empty($order->card_id)) {
            usort($cards, function($a, $b) use ($order) {
                return intval($b->id == $order->card_id) - intval($a->id == $order->card_id);
            });            
        }
        
        $clear_cards = [];
        foreach ($cards as $card)
            if (!isset($clear_cards[$card->pan]))
                $clear_cards[$card->pan] = $card;
                
        return $clear_cards;
    }
    
    /**
     * RecurrentsScript::recurent()
     * 
     * @param mixed $params
     * {@params['amount']} - сумма списания
     * {@params['order']} 
     * {@params['']}
     * @return string $status
        // APPROVED - успешно списали
        // NOMONEY - нет денег, продолжаем списание
        // REJECTED - перестаем списывать с этой карты
        // TIMEOUT - перестаем списывать со всех карт

     */
    private function recurent($params)
    {
        $desc = $params['percent'].' ('.$params['amount'].') ';
        
//        $test_results = $this->get_test_results();
//        $result = mt_rand(0, 0);        
//        $b2p_response = $test_results[$result];

        $b2p_response = $this->best2pay->recurrent_purchase_by_token($params);
        
        $xml = simplexml_load_string($b2p_response);

        if ($xml->state == 'APPROVED') {
            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Успешно списали '.$desc.' с карты '.$params['card_pan']);echo '</pre><hr />';
            $status = 'APPROVED';
        } elseif ($xml->state == 'REJECTED' && $xml->message == 'Insufficient funds') {
            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Не списали Нет денег '.$desc.' с карты '.$params['card_pan']);echo '</pre><hr />';
            $status = 'NOMONEY';
        } elseif ($xml->state == 'REJECTED' && in_array(strval($xml->message), $this->block_messages)) {
//            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Не списали Отказ банка '.$desc.' с карты '.$params['card_pan']);echo '</pre><hr />';
            
            $block_date = date('Y-m-d H:i:s', time() + $this->block_card_hours * 3600);
            $this->best2pay->update_card($params['card_id'], [
                'next_recurrent_date' => $block_date
            ]);
            $status = 'DECLINE';
        } elseif ($xml->state == 'TIMEOUT' || $xml->state == 'ERROR') {
            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Не списали Останавливаем списание '.$desc.' со всех карт '.$params['card_pan']);echo '</pre><hr />';
            $status = 'TIMEOUT';
        } else {
            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('Не списали Останавливаем списание '.$desc.' с карты '.$params['card_pan']);echo '</pre><hr />';
            $status = 'REJECTED';
        }

        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($b2p_response);echo '</pre><hr />';
        return $status;
    }
    

    
    private function get_test_results()
    {
        $test_results = [
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>791792876</order_id>
<order_state>COMPLETED</order_state>
<reference>377070</reference>
<id>1195873335</id>
<date>2023.08.26 14:05:21</date>
<type>PURCHASE</type>
<state>APPROVED</state>
<reason_code>1</reason_code>
<message>Successful financial transaction</message>
<name>P2P Transfer</name>
<pan>427644******2270</pan>
<amount>206400</amount>
<fee>10320</fee>
<currency>643</currency>
<approval_code>204999</approval_code>
<token>d8befdd2-0241-4d5b-bb7a-2f1de4a92e45</token>
<expdate>01/2024</expdate>
<signature>OWI4NTM5ZmJkNGE0MGIzYjVjYzQ4NTZiNmFhY2Q3MWU=</signature>
</operation>',
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>791806485</order_id>
<order_state>REGISTERED</order_state>
<reference>530490</reference>
<id>1195890232</id>
<date>2023.08.26 14:17:47</date>
<type>AUTHORIZE</type>
<state>REJECTED</state>
<reason_code>6</reason_code>
<message>Insufficient funds</message>
<name>UNKNOWN NAME</name>
<pan>427666******3990</pan>
<amount>100</amount>
<fee>0</fee>
<currency>643</currency>
<expdate>09/2023</expdate>
<client_ref>530490</client_ref>
<signature>ZmZmZGY4OTRhMzRhMTc1ZjlkOTFmODkwNjMxN2M1ZTU=</signature>
</operation>
',
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>791796828</order_id>
<order_state>REGISTERED</order_state>
<reference>592297</reference>
<id>1195878724</id>
<date>2023.08.26 14:09:25</date>
<type>AUTHORIZE</type>
<state>REJECTED</state>
<reason_code>15</reason_code>
<message>Black list bin checkup failure</message>
<name>UNKNOWN NAME</name>
<pan>220412******3109</pan>
<amount>100</amount>
<fee>0</fee>
<currency>643</currency>
<expdate>01/2028</expdate>
<client_ref>592297</client_ref>
<signature>OTEwNjBiMjg0MWJiNTNmMTI5MjdkOWQwOTFkMjQyZmM=</signature>
</operation>
',
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>665636618</order_id>
<order_state>REGISTERED</order_state>
<reference>f110d762-4787-4e38-aa2f-58d4555e8383</reference>
<id>1014901007</id>
<date>2023.04.13 10:57:32</date>
<type>PURCHASE</type>
<state>TIMEOUT</state>
<reason_code>13</reason_code>
<message>Timeout</message>
<name>UNKNOWN NAME</name>
<pan>220220******8387</pan>
<amount>301000</amount>
<currency>643</currency>
<signature>YmRiN2E1Mjk1ZDg3ZGE2YzljZWJkZTZjODY2ZjIwY2Y=</signature>
</operation>
',
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>675816341</order_id>
<order_state>REGISTERED</order_state>
<reference>3e2abea1-2ad3-49d1-a0a8-ca5993e3d9fb</reference>
<id>1030343512</id>
<date>2023.04.24 16:30:52</date>
<type>PURCHASE</type>
<state>ERROR</state>
<reason_code>5</reason_code>
<message>Invalid transaction. Decline by Issuer.</message>
<name>UNKNOWN NAME</name>
<pan>546930******1408</pan>
<amount>82000</amount>
<currency>643</currency>
<signature>MzFjOTc2YzA4ODY2NmYzYjM3MTc5MzMyYzI2YjBlYTQ=</signature>
</operation>
',
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>791806485</order_id>
<order_state>REGISTERED</order_state>
<reference>530490</reference>
<id>1195890232</id>
<date>2023.08.26 14:17:47</date>
<type>AUTHORIZE</type>
<state>REJECTED</state>
<reason_code>6</reason_code>
<message>Insufficient funds</message>
<name>UNKNOWN NAME</name>
<pan>427666******3990</pan>
<amount>100</amount>
<fee>0</fee>
<currency>643</currency>
<expdate>09/2023</expdate>
<client_ref>530490</client_ref>
<signature>ZmZmZGY4OTRhMzRhMTc1ZjlkOTFmODkwNjMxN2M1ZTU=</signature>
</operation>
',
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>791806485</order_id>
<order_state>REGISTERED</order_state>
<reference>530490</reference>
<id>1195890232</id>
<date>2023.08.26 14:17:47</date>
<type>AUTHORIZE</type>
<state>REJECTED</state>
<reason_code>6</reason_code>
<message>Insufficient funds</message>
<name>UNKNOWN NAME</name>
<pan>427666******3990</pan>
<amount>100</amount>
<fee>0</fee>
<currency>643</currency>
<expdate>09/2023</expdate>
<client_ref>530490</client_ref>
<signature>ZmZmZGY4OTRhMzRhMTc1ZjlkOTFmODkwNjMxN2M1ZTU=</signature>
</operation>
',
            '<?xml version="1.0" encoding="UTF-8"?><operation>
<order_id>791792876</order_id>
<order_state>COMPLETED</order_state>
<reference>377070</reference>
<id>1195873335</id>
<date>2023.08.26 14:05:21</date>
<type>PURCHASE</type>
<state>APPROVED</state>
<reason_code>1</reason_code>
<message>Successful financial transaction</message>
<name>P2P Transfer</name>
<pan>427644******2270</pan>
<amount>206400</amount>
<fee>10320</fee>
<currency>643</currency>
<approval_code>204999</approval_code>
<token>d8befdd2-0241-4d5b-bb7a-2f1de4a92e45</token>
<expdate>01/2024</expdate>
<signature>OWI4NTM5ZmJkNGE0MGIzYjVjYzQ4NTZiNmFhY2Q3MWU=</signature>
</operation>',
        ];
        
        return $test_results;
    }
}
new RecurrentsMakerScript();
