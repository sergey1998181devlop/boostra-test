<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '3600');

require_once __DIR__.'/../api/Simpla.php';

class RecurrentsExchangeCron extends Simpla
{    
    public function __construct()
    {
    	parent::__construct();
        
        // получение новых портфелей
        $this->create_recurrents_list();
        
        // получение договоров по не загруженным портфелям
        $this->load_recurrents();
        
        // отправление результатов в 1с
        $this->send_reports();
    }
    
    private function send_reports()
    {
        while ($list = $this->recurrents->get_list_for_report()) {
            $res = $this->soap->DataForRecurrentLoansResult($list);
            if (!empty($res->return) && $res->return == 'ОК') {
                $this->recurrents->update_list($list->id, [
                    'sent_1c' => 2,
                    'sent_date' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->recurrents->update_list($list->id, [
                    'sent_1c' => 3,
                    'sent_date' => date('Y-m-d H:i:s'),
                ]);
                
            }
        }

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($report);echo '</pre><hr />';
    }
    
    private function load_recurrents()
    {
        while ($list = $this->recurrents->get_list(['loaded'=>0])) {
            $json_loans = $this->soap->DataForRecurrentLoans($list->list_uid);

            $loans = json_decode($json_loans->return);
            if (!empty($loans->Займы)) {
                foreach ($loans->Займы as $item) {
                    
                    if (!$this->recurrents->get_recurrent(['list_id'=>$list->id, 'number'=>$item->Номер])){

                        $paymentType = 'closed';
                        if ($item->ТипПлатежа == 'НаПролонгацию') {
                            $paymentType = 'prolongation';
                        }
                        $this->recurrents->add_recurrent([
                            'list_id' => $list->id,
                            'number' => $item->Номер,
                            'od' => $item->СуммаОД,
                            'percents' => $item->СуммаПроцентов,
                            'client_uid' => $item->УИДКлиента,
                            'created' => date('Y-m-d'),
                            'payment_type' => $paymentType
                        ]);
                    }
                }
                $this->recurrents->update_list($list->id, [
                    'loaded' => 2
                ]);
            } else {
                $this->recurrents->update_list($list->id, [
                    'loaded' => 3
                ]);                
            }
            
        }
    }
    

    
    private function create_recurrents_list()
    {
        $resp = $this->soap1c->DataForRecurrent();
        if (!empty($resp->return)) {
            $recurrents_list = json_decode($resp->return);
            if (!empty($recurrents_list)) {
                foreach ($recurrents_list as $list_uid) {
                    if (!$this->recurrents->get_list(['list_uid'=>$list_uid])) {
                        $this->recurrents->add_list([
                            'list_uid' => $list_uid,
                            'created' => date('Y-m-d H:i:s'),
                        ]);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($list_uid);echo '</pre><hr />';
                    }
                }
            }

        }
    }
    
}
new RecurrentsExchangeCron();