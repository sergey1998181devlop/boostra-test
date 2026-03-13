<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 500);

require_once dirname(__FILE__).'/../api/Simpla.php';

class NbkiReportCron extends Simpla
{
    private $nbki_dir = 'files/nbki/';
    private $filename = '';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->create_p2p();
        $this->create_pay();
        $this->create_report();
    }

    private function create_p2p()
    {
        while($p2p_items = $this->get_p2p_items()) {
            foreach ($p2p_items as $item) {
                $order = $this->orders->get_order($item->order_id);
                $this->nbki_report->add_item([
                    'user_id' => $item->user_id,
                    'order_id' => $item->order_id,
                    'contract_number' => $this->contracts->create_number($order),
                    'type' => 'P2P',
                    'external_id' => $item->id,
                    'created' => date('Y-m-d H:i:s'),
                    'operation_date' => $item->complete_date,
                ]);

                $this->best2pay->update_p2pcredit($item->id, ['nbki_ready' => 2]);
            }
        }
    }

    private function create_pay()
    {
        $this->nbki_report->reset_fail_pay_items();
        
        while ($pay_items = $this->get_pay_items()) {
            foreach ($pay_items as $item) {
                $payNumber = $this->best2pay->get_payment_number($item);
                $onec_response = $this->soap1c->GetDetailPay($payNumber);
                sleep(1);
                if (!empty($onec_response->return)) {
                    if ((empty($item->order_id) || empty($item->user_id)) && !empty($item->contract_number)) {
                        $contract = $this->contracts->get_contract_by_params(['number'=>$item->contract_number]);
                        $item->order_id = $contract->order_id;
                        $item->user_id = $contract->user_id;
                    }
                    
                    $onec_data = json_decode($onec_response->return);
                    if (!empty($onec_data->СледующаяПлановаяДата) && ($onec_data->ОплатаОД > 0 || $onec_data->ОплатаПроцентов > 0)) {
                        $this->nbki_report->add_item([
                            'user_id' => $item->user_id,
                            'order_id' => $item->order_id,
                            'contract_number' => $item->contract_number,
                            'type' => 'PAY',
                            'external_id' => $item->id,
                            'created' => date('Y-m-d H:i:s'),
                            'onec_data' => $onec_response->return,
                            'operation_date' => $item->operation_date,
                        ]);
                        
                        $this->best2pay->update_payment($item->id, ['nbki_ready' => 2]);
                    } else {
                        $this->best2pay->update_payment($item->id, ['nbki_ready' => 4]);
                    }
                } else {
                    $this->best2pay->update_payment($item->id, ['nbki_ready' => 3]);                    
                }
            }
        }
        
    }
    
    private function create_report()
    {
        if ($this->need_create_report()) {
            if ($items = $this->nbki_report->get_report_items()) {
                $report_items = [];
                
                foreach ($items as $item) {
                    switch ($item->type) :
                        case 'P2P':
                            break;
                        case 'PAY':
                            $item->onec_data = json_decode($item->onec_data);
                            break;
                    endswitch;
                    $item_key = $item->type.'_'.date('Ymd', strtotime($item->operation_date)).'_'.$item->contract_number;
                    
                    if (isset($report_items[$item_key])) {
                        if (!empty($item->onec_data)) {
                            $report_items[$item_key]->onec_data->ОплатаОД += $item->onec_data->ОплатаОД;
                            $report_items[$item_key]->onec_data->ОплатаПроцентов += $item->onec_data->ОплатаПроцентов;
                            $report_items[$item_key]->onec_data->Закрыт = $report_items[$item_key]->onec_data->Закрыт ? $report_items[$item_key]->onec_data->Закрыт : $item->onec_data->Закрыт;
                            $report_items[$item_key]->onec_data->ВсегоОплатаОД = max($report_items[$item_key]->onec_data->ВсегоОплатаОД, $item->onec_data->ВсегоОплатаОД);
                            $report_items[$item_key]->onec_data->ВсегоОплатаПроцентов = max($report_items[$item_key]->onec_data->ВсегоОплатаПроцентов, $item->onec_data->ВсегоОплатаПроцентов);
                            $report_items[$item_key]->onec_data->ОстатокОД = min($report_items[$item_key]->onec_data->ОстатокОД, $item->onec_data->ОстатокОД);
                            $report_items[$item_key]->onec_data->ОстатокПроцентов = min($report_items[$item_key]->onec_data->ОстатокПроцентов, $item->onec_data->ОстатокПроцентов);
                            $report_items[$item_key]->onec_data->СледующаяПлановаяДата = $item->onec_data->СледующаяПлановаяДата;                        
                        }
                    } else {
                        $item->nbki_items = [];
                        $report_items[$item_key] = $item;
                    }
                    $report_items[$item_key]->nbki_items[] = $item->id;
                }
                unset($items);
                                
                foreach ($report_items as $report_item) {
                    if (empty($report_item->order_id) || empty($report_item->user_id)) {
                        continue;
                    }
                    
                    $report_item->user = $this->nbki_report->get_user_for_nbki((int)$report_item->user_id);
                    $report_item->order = $this->nbki_report->get_order_for_nbki((int)$report_item->order_id);
                    
                    // фикс для пересортицы ОД-процентов
                    if ($report_item->type == 'PAY') {
                        if ($report_item->onec_data->ОстатокПроцентов < 0 && (abs($report_item->onec_data->ОстатокПроцентов) == abs($report_item->onec_data->ОстатокОД))) {
                            $rest = abs($report_item->onec_data->ОстатокПроцентов);
                            $report_item->onec_data->ВсегоОплатаОД += $rest;
                            $report_item->onec_data->ВсегоОплатаПроцентов -= $rest;
                            $report_item->onec_data->ОстатокОД = 0;
                            $report_item->onec_data->ОстатокПроцентов = 0;
                            $report_item->onec_data->Закрыт = true;
                        } else {
                            $report_item->onec_data->ОстатокПроцентов = max(0, $report_item->onec_data->ОстатокПроцентов);
                        }
                    }
                }

                // передача всех операций сразу
                //$this->send_items_all($report_items);
                
                // передача операций по частям
                $this->send_items_chunk($report_items);
            }
        }
    }
    
    private function send_items_all()
    {
        $report = $this->nbki_report->send_items($report_items);

        if (!empty($report->filename)) {
            $this->save_report($report);

            $report_id = $this->nbki_report->add_report([
                'name' => $report->filename,
                'filename' => $report->filename,
                'created' => date('Y-m-d H:i:s'),
            ]);
            
            foreach ($report_items as $report_item) {
                foreach ($report_item->nbki_items as $item_id) {
                    $this->nbki_report->update_item($item_id, [
                        'report_id' => $report_id
                    ]);
                }
            }
        }
    }
    
    private function send_items_chunk($report_items)
    {
        $prepare_reports = array_chunk($report_items, 1000, true);
        foreach ($prepare_reports as $k => $prepare_report) {
            
            $prepare_report = array_filter($prepare_report, function($item){
                return trim(mb_strtolower($item->user->lastname)) != 'тест'
                    && trim(mb_strtolower($item->user->firstname)) != 'тест'
                    && trim(mb_strtolower($item->user->patronymic)) != 'тест';                    
            });
            
            $report = $this->nbki_report->send_items($prepare_report);
    
            if (!empty($report->filename)) {
                $this->save_report($report);
    
                $report_id = $this->nbki_report->add_report([
                    'name' => date('d.m.Y').'-'.($k+1),
                    'filename' => $this->filename,
                    'created' => date('Y-m-d H:i:s'),
                ]);

                foreach ($prepare_report as $prepare_report_item) {
                    foreach ($prepare_report_item->nbki_items as $item_id) {
                        $this->nbki_report->update_item($item_id, [
                            'report_id' => $report_id
                        ]);
                    }
                }
            }
        }
    }
    
    public function save_report($report)
    {
        $year_dir = date('Y').'/';
        $month_dir = $year_dir.date('m').'/';
        $this->filename = $month_dir.$report->filename;
        
        $year_dir = $this->config->root_dir.$this->nbki_dir.$year_dir;
        if (!file_exists($year_dir)){
echo 'create'.$year_dir;
            mkdir($year_dir, 0775);
        }
        $month_dir = $this->config->root_dir.$this->nbki_dir.$month_dir;
        if (!file_exists($month_dir)){
echo 'create'.$month_dir;
            mkdir($month_dir, 0775);
        }        
        
        if (!file_exists($month_dir)) {
            throw new Exception('Нет директории '.$month_dir);
        }
        
        $fp = fopen($this->config->root_dir.$this->nbki_dir.$this->filename, 'w+');
        flock($fp, LOCK_EX);

        fwrite($fp, iconv('utf8', 'cp1251', $report->data));
        flock($fp, LOCK_UN);
    }

    private function need_create_report()
    {
//return true;
        $report_date = date('Y-m-d');
//        $report_date = date('2024-02-06'); 
        if (!$this->nbki_report->get_day_report($report_date)) {
            // записываем после часа ночи
            $current_hour = date('H');
            if ($current_hour > 0) {
                return true;
            }
        }
                
        return false;
    }
    
    private function get_p2p_items()
    {
        $this->db->query("
            SELECT p.* 
            FROM b2p_p2pcredits AS p
            LEFT JOIN s_orders AS o 
            ON o.id = p.order_id
            WHERE p.nbki_ready = 0
            AND p.status = 'APPROVED'
            AND o.organization_id = ?
            LIMIT 10
        ", $this->organizations::AKVARIUS_ID);
        
        return $this->db->results();
    }

    private function get_pay_items()
    {
        $this->db->query("
            SELECT p.* 
            FROM b2p_payments AS p
            LEFT JOIN s_orders AS o 
            ON o.id = p.order_id
            WHERE p.nbki_ready = 0
            AND p.reason_code = 1
            AND p.sent = 1
            AND p.payment_type = 'debt'
            AND o.organization_id = ?
            LIMIT 10
        ", $this->organizations::AKVARIUS_ID);
        
        return $this->db->results();
    }
}
new NbkiReportCron();