<?php
error_reporting(-1);

session_start();

ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class CDoctorCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    public function run()
    {
        $from = date('Y-m-d H:i:s', time() - 2*86400);
        if ($new_items = $this->cdoctor->get_items(array('status' => 'new', 'from' => $from)))
        {
            foreach ($new_items as $item)
            {
                if ($cdoctor_item = $this->cdoctor->check_status($item->cdoctor_id))
                {
                    if ($cdoctor_item->status == 'unpaid')
                    {
                        $this->cdoctor->update_item($item->id, array(
                            'cdoctor_status' => 'unpaid',
                        ));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($cdoctor_item, $item);echo '</pre><hr />';                
//exit;                        
                    }
                    elseif ($cdoctor_item->status == 'paid')
                    {
                        $this->cdoctor->update_item($item->id, array(
                            'cdoctor_status' => $cdoctor_item->status,
                            'amount' => $cdoctor_item->amount,
                            'payout' => $cdoctor_item->payoutAmount,
                            'pdf' => $cdoctor_item->pdfFile
                        ));
                        
                        // создаем новую заявку
                        if ($old_order = $this->orders->get_order($item->order_id))
                        {
                            $order = array(
                                'cdoctor_id' => $item->id,
            					'user_id' => $item->user_id,
                                'card_id' => $old_order->card_id,
            					'amount' => $cdoctor_item->payoutAmount,
            					'period' => 7,
                                'percent' => 0,
                                'date' => date('Y-m-d H:i:s'),
            					'comment' => '',
                                'ip' => '',
                                'juicescore_session_id' => '',
                                'local_time' => '',
                                'utm_source' => 'cdoctor',
                                'max_amount' => 0,
                                'razgon' => 0,
                                'manager_id' => 50,
                            );
                            if ($order_id = $this->orders->add_order($order))
                            {
                                // отправляем ее в 1с 
                                $soap_zayavka = $this->soap->soap_repeat_zayavka($cdoctor_item->payoutAmount, 7, $item->user_id, $old_order->card_id);
                                if (empty($soap_zayavka->return->id_zayavka))
                                {
                                    $this->orders->update_order($order_id, array('status'=>3, 'note' => strval($soap_zayavka->return->Error)));
                                    $this->leadgid->reject_actions($order_id);
                                }
                                else
                                {
                                    // задержка для 1с между подачей заявки и одобрением
                                    sleep(3);

                                    // ставим статус одобрена и отправляем статус в 1с
                                    $this->orders->update_order($order_id, array(
                                        'status'=>2, 
                                        '1c_id' => $soap_zayavka->return->id_zayavka,
                                        'approve_date' => date('Y-m-d H:i:s'),            
                                    ));
                                
                                    $tech_manager = $this->managers->get_manager(50);
                                    $this->soap->update_status_1c($soap_zayavka->return->id_zayavka, 'Одобрено', $tech_manager->name_1c, $cdoctor_item->payoutAmount, 0, '', 1);
                                    
                                    // ставим клиенту левел
                                    $this->users->update_user($item->user_id, array(
                                        'cdoctor_level' => $cdoctor_item->level,
                                        'cdoctor_pdf' => $cdoctor_item->pdfFile
                                    ));

                                    $site_id = $this->users->get_site_id_by_user_id($item->user_id);
                                    $template = $this->sms->get_template($this->sms::SMS_TEMPLATE_APPROVE_OTHER, $site_id);
                                    $message = strtr($template->template, [
                                        '{{amount}}' => $cdoctor_item->payoutAmount,
                                    ]);

                            		//отправка смс
                            		$status = $this->smssender->send_sms($old_order->phone_mobile, $message, $site_id);
                            		if($status){
                            			$this->db->query("INSERT INTO sms_log SET phone='".$old_order->phone_mobile."', status='".$status[1]."', dates='".date("Y-m-d H:i:s")."', sms_id='".$status[0]."'");
                            		}

                                }
    
                                
                                
                            }
                            
                            // отправляем чек
                            $cdoctor = $this->cdoctor->get_item($item->id);
                            $this->cloudkassir_lagutkin->send_receipt_cdoctor($cdoctor);
                        }
                    }
                }
            }
        }
    }
    
    public function test()
    {
    	$order_id = '171989';
        
//        $return = $this->cdoctor->send_order($order_id);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($return);echo '</pre><hr />';

        $return = $this->cdoctor->check_status('6');
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($return);echo '</pre><hr />';
    }
    
}

new CDoctorCron();