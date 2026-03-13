<?php
error_reporting(-1);

session_start();

ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';
require_once dirname(__FILE__).'/../api/Scorings.php';

class AutoapproveCron extends Simpla
{
    private $wait_scorings = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_AXILINK_2
    ];

    private $critical_scorings = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_AXILINK_2
    ];

    private $wait_scoring_statuses = [
        Scorings::STATUS_NEW,
        Scorings::STATUS_PROCESS,
        Scorings::STATUS_IMPORT,
    ];

    private $fail_scoring_statuses = [
        Scorings::STATUS_ERROR,
        Scorings::STATUS_PROCESS,
        Scorings::STATUS_IMPORT
    ];

    public function __construct()
    {
    	parent::__construct();

        $this->run();
    }
    

    private function log_autoretry($order_id, $manager_id) {
        $this->changelogs->add_changelog(array(
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'autoretry',
            'old_values' => serialize(array()),
            'new_values' => serialize(array('autoretry' => 0)),
            'order_id' => $order_id,
        ));
    }
    
    public function run()
    {
        $query = $this->db->placehold("
            SELECT id
            FROM __orders
            WHERE autoretry = 2
            ORDER BY date ASC
            LIMIT 50
        ");
        $this->db->query($query);
        if ($orders = $this->db->results('id')) {
            $system_manager = $this->managers->get_manager(50);

            $reasons = [];
            foreach ($this->reasons->get_reasons() as $r) {
                $reasons[$r->id] = $r;
            }

            foreach ($orders as $order_id) {
                $order = $this->orders->get_order((int)$order_id);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order);echo '</pre><hr />';
                if ($order->status == 3) {
                    // записываем в статистику отказ
                    $this->orders->update_order($order_id, ['autoretry' => 0]);
                    $this->add_statistic($order_id, 'Отказ', $reasons[$order->reason_id]->admin_name);

                } elseif (strtotime($order->date) < (time() - 600)) {
                    // переводим на верификацию запаздалые заявки
                    $this->orders->update_order($order_id, ['autoretry' => 0]);
                    $this->add_statistic($order_id, 'Верификация', 'Истекло время ожидания');

                } else {
                    $scorings = $this->get_scorings($order_id);
                    $have_process_scorings = $this->check_process_scorings($scorings);
                    
                    if (empty($have_process_scorings)) {
                        
                        if ($have_fail_scorings = $this->check_fail_scorings($scorings)) {
                            $this->orders->update_order($order_id, ['autoretry' => 0]);
                            $this->add_statistic($order_id, 'Верификация', 'Есть не пройденные скоринги');                            
                        } else {
                            if ($scorings['scorista']->scorista_status == 'Одобрено') {
                                
                                $max_amount = $this->get_max_amount($scorings['scorista']);
                                
                                $this->orders->update_order($order_id, ['autoretry' => 0]);
                                $this->add_statistic($order_id, 'Одобрение', 'Сумма одобрения: '.$max_amount);                            
                                
                            } else {
                                $this->orders->update_order($order_id, ['autoretry' => 0]);
                                $this->add_statistic($order_id, 'Верификация', 'Скориста не одобрена');                                
                            }
                            
                        }
                    }
                }
            }
            
        }
        
    }
    
    private function check_process_scorings($scorings)
    {
        $have_process_scorings = 0;
        foreach ($scorings as $scoring) {
            if (in_array($scoring->type, $this->wait_scorings) && in_array($scoring->status, $this->wait_scoring_statuses)) {
                $have_process_scorings = 1;
            }
        }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$have_process_scorings', $have_process_scorings, $scoring->order_id);echo '</pre><hr />';        
        return $have_process_scorings;
    }
    
    private function check_fail_scorings($scorings)
    {
        $have_fail_scorings = 0;
        foreach ($scorings as $scoring) {
            if (in_array($scoring->type, $this->critical_scorings)) {
                if ($scoring->status == Scorings::STATUS_ERROR || ($scoring->status == Scorings::STATUS_COMPLETED && $scoring->success == 0)) {
                    $have_fail_scorings = 1;
                }                
            }
        }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$have_fail_scorings', $have_fail_scorings, $scoring->order_id);echo '</pre><hr />';        
        return $have_fail_scorings;
    }
    
    private function get_scorings($order_id)
    {
        $scorings = [];
        foreach ($this->scorings->get_scorings(['order_id'=>$order_id]) as $sc) {
            $scorings[$sc->type] = $sc;
        }
        
        return $scorings;
    }
    
    private function add_statistic($order_id, $decision, $reason = '')
    {
        $this->db->query("
            INSERT INTO s_dbrain_statistics
            SET order_id = ?, decision = ?, reason = ?, created = ?
        ", (int)$order_id, (string)$decision, (string)$reason, date('Y-m-d H:i:s'));

echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order_id, $decision, $reason);echo '</pre><hr />';
    }
    
    private function get_max_amount($scorista)
    {
        $last_scorista = $scorista;
        
        $last_scorista->body = json_decode($this->scorings->get_scoring_body($last_scorista->id));
        $decisionSum_without_PTI = $last_scorista->body->additional->decisionSum_without_PTI ?? null;
        $decisionSum = $last_scorista->body->additional->decisionSum ?? null;
        $has_two_decisionSum = !empty($decisionSum_without_PTI) && !empty($decisionSum);

        if ($has_two_decisionSum) {
            $last_scorista_max_amount = max($decisionSum, $decisionSum_without_PTI);
        } else {
            $last_scorista_max_amount = $last_scorista->body->additional->decisionSum;
        }

        if ($last_scorista_max_amount > 0)
        {
            $max_amount = min(9900, intval($last_scorista_max_amount / 1000) * 1000);

            return $max_amount;


/*
            $this->orders->update_order($order->id, $update);
            
            $this->changelogs->add_changelog(array(
                'manager_id' => $system_manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'status',
                'old_values' => serialize(array()),
                'new_values' => serialize($update),
                'order_id' => $order->id,
            ));
            $this->soap->update_status_1c($order->id_1c, 'Одобрено', $system_manager->name_1c, $order->amount, $order->percent, '', 0, $order->period);
    		

    		$user = $this->users->get_user($order->user_id);

            $sms_approve_status = $this->settings->sms_approve_status;
            if(!empty($sms_approve_status)) {
                $template = $this->sms->get_template($this->sms::AUTO_APPROVE_TEMPLATE_NOW);
                $text_message = strtr($template->template, [
                    '{{firstname}}' => $user->firstname,
                    '{{amount}}' => $order->approve_amount ?: $order->amount,
                ]);

                $text = iconv('UTF-8', 'cp1251', $text_message);
                $resp = $status = $this->smssender->send_sms($user->phone_mobile, $text);
                $this->sms->add_message(
                    [
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'phone' => $user->phone_mobile,
                        'message' => $text_message,
                        'created' => date('Y-m-d H:i:s'),
                        'send_status' => $resp[1],
                        'delivery_status' => '',
                        'send_id' => $resp[0],
                        'type' => $this->smssender::TYPE_AUTO_APPROVE_ORDER,
                    ]
                );

                if($status){
                    $this->db->query("INSERT INTO sms_log SET phone='".$user->phone_mobile."', status='".$status[1]."', dates='".date("Y-m-d H:i:s")."', sms_id='".$status[0]."'");
                }
            }
*/                                    
        }
    }
        
}

new AutoapproveCron();