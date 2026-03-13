<?php

require_once 'Simpla.php';
require_once 'Scorings.php';

class ScorApprove extends Simpla
{
    private $order;
    private $order_scorings = [];
    
    private $required_scorings = [
        // TODO: Вероятно нужен рефакторинг, если ScorApprove будет включаться обратно
        Scorings::TYPE_AGE,
        Scorings::TYPE_BLACKLIST,
//        Scorings::TYPE_EFRSB,
        Scorings::TYPE_FNS,
        Scorings::TYPE_LOCATION,
    ];
    
    public function check($order_id)
    {
        return $this->return_false("disabled");

        if ($this->init($order_id)) {
            $has_bad_axi = false;
            if (!$this->scorings->isScoristaAllowed($order_id)) {
                $has_bad_axi = empty($this->order_scorings[$this->scorings::TYPE_AXILINK_2]->success);
            }

            $scoring = $this->order_scorings[$this->scorings::TYPE_SCORISTA] ?? null;
            if (!$has_bad_axi &&
                !empty($scoring->success)
                && empty($scoring->manual)
                && ($this->scorings->get_body_by_type($scoring)->additional->no_need_for_underwriter ?? false)
            ) {
                $this->approve();
            } else {
                return $this->return_false(
                    'check failed ' . ($scoring->manual ?? 'null') .
                    ' ball ' . ($scoring->scorista_ball ?? 'null') .
                    ' no_need_for_underwriter ' . ($this->scorings->get_body_by_type($scoring)->additional->no_need_for_underwriter ?? 'null')
                );
            }
        }
    }
    
    private function init($order_id)
    {
        if ($this->settings->scor_approve_counter < 1) {
            return $this->return_false('scor_approve_counter');
        }    
        
        if (!($this->order = $this->orders->get_order($order_id))) {
            return $this->return_false('empty order');
        }
        
        if ($this->order->status != $this->orders::ORDER_STATUS_CRM_NEW) {
            return $this->return_false('order status not new: '.$this->order->status);
        }
        
        if (!empty($this->order->manager_id) && $this->order->manager_id != $this->managers::MANAGER_SYSTEM_ID) {
            return $this->return_false('order manager issued: '.$this->order->manager_id);
        }

//      Включены все источники
//      if (!in_array($this->order->utm_source, $this->utm_source_list)) {
//          return $this->return_false('utm_source: '.$this->order->utm_source);
//      }
        
        if ($this->order->loan_type == 'IL') {
            return $this->return_false('loan_type IL');
        }
        
        foreach ($this->scorings->get_scorings(['order_id' => $this->order->order_id]) as $sc) {
            $this->order_scorings[$sc->type] = $sc;
        }
        if (!$this->check_required_scorings()) {
            return $this->return_false('check_required_scorings');
        }
        
        return true;
    }
    
    private function check_required_scorings()
    {
        foreach ($this->required_scorings as $scoring_type) {
            // ФНС не проводится у ПК клиентов
            if ($scoring_type == Scorings::TYPE_FNS && $this->order->have_close_credits == 1) {
                continue;
            }

            if (empty($this->order_scorings[$scoring_type]->success)) {
                return false;
            }
        }

        return true;
    }

    private function approve()
    {
        $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);
        
        $update = [
            'status' => 2,
            'manager_id' => $tech_manager->id,
            'approve_date' => date('Y-m-d H:i:s'),
        ];

        $old_values = [];
        foreach ($update as $key => $val) {
            if ($this->order->$key != $val) {
                $old_values[$key] = $this->order->$key;
                $this->order->$key = $val;
            }
        }

        $log_update = [];
        foreach ($update as $key => $val) {
            if (isset($old_values[$key])) {
                $log_update[$key] = $val;
            }
        }

        $this->changelogs->add_changelog([
            'manager_id' => $tech_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => $this->order->order_id,
        ]);

        $this->orders->update_order($this->order->order_id, $update);
        $order = $this->orders->get_order((int)$this->order->order_id);

        $this->soap->update_status_1c(
            $order->id_1c, 
            'Одобрено', 
            $tech_manager->name_1c, 
            $order->amount, 
            $order->percent, 
            '', 
            0, 
            $order->period
        );
        
        $this->send_sms($order);
        
        $this->users->update_loan_funnel_report(
            (int)$order->order_id,
            (int)$order->user_id,
            [
                'approved' => true,
                'approved_date' => date("Y-m-d")
            ]
        );

        $this->finroznica->send_user($this->users->get_user($order->user_id));

        $this->cross_orders->create($order->order_id);    

        $this->settings->scor_approve_counter -= 1;
        $this->order_data->set($this->order->order_id, $this->order_data::SCOR_APPROVE, 1);

        $this->logging('', '', $this->order->order_id, $update, 'scor_approve.txt');
    }
    
    private function send_sms($order)
    {
        //отправка смс одобрение
        $sms_approve_status = $this->settings->sms_approve_status;
        if (!empty($sms_approve_status)) {
            $site_id = $this->users->get_site_id_by_user_id($order->user_id);
            $template = $this->sms->get_template($this->sms::AUTO_APPROVE_TEMPLATE_NOW, $site_id);
            $text_message = strtr($template->template, [
                '{{firstname}}' => $order->firstname,
                '{{amount}}' => $order->approve_amount ?: $order->amount,
            ]);

            $text = $text_message;
            $resp = $status = $this->smssender->send_sms($order->phone_mobile, $text, $site_id);
            $this->sms->add_message(
                [
                    'user_id' => $order->user_id,
                    'order_id' => $order->order_id,
                    'phone' => $order->phone_mobile,
                    'message' => $text_message,
                    'created' => date('Y-m-d H:i:s'),
                    'send_status' => $resp[1],
                    'delivery_status' => '',
                    'send_id' => $resp[0],
                    'type' => $this->smssender::TYPE_AUTO_APPROVE_ORDER,
                ]
            );

            if ($status) {
                $this->db->query("INSERT INTO sms_log SET phone='" . $order->phone_mobile . "', status='" . $status[1] . "', dates='" . date("Y-m-d H:i:s") . "', sms_id='" . $status[0] . "'");
            }
        }
    }
    
    private function return_false($msg)
    {
        $this->logging('', '', $this->order->order_id, $msg, 'scor_approve.txt');
        return false;
    }
}