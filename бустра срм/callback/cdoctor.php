<?php
session_start();

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';

class CDoctorCallback extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
        
/*
        if ($this->request->method('post'))
            $this->run();
        else
            exit('ERROR METHOD');
*/
    }
    
    private function run()
    {
        $json = $this->request->post();
        
        $params = (object)json_decode($json);
        
        if ($item = $this->cdoctor->get_cdoctor_item($params->id))
        {
            if ($item->cdoctor_status == 'new' && $params->status == 'paid')
            {
                    $this->cdoctor->update_item($item->id, array(
                        'cdoctor_status' => $params->status,
                        'amount' => $params->amount,
                        'payout' => $params->payoutAmount,
                        'pdf' => $params->pdfFile
                    ));
                    
                    // создаем новую заявку
                    if ($old_order = $this->orders->get_order($item->order_id))
                    {
                        $order = array(
                            'cdoctor_id' => $item->id,
        					'user_id' => $item->user_id,
                            'card_id' => $old_order->card_id,
        					'amount' => $params->payoutAmount,
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
                            $soap_zayavka = $this->soap->soap_repeat_zayavka($params->payoutAmount, 7, $item->user_id, $old_order->card_id, 0);
                            if (empty($soap_zayavka->return->id_zayavka))
                            {
                                $this->orders->update_order($order_id, array('status'=>3, 'note' => strval($soap_zayavka->return->Error)));
                                $this->leadgid->reject_actions($order_id);
                            }
                            else
                            {
                                sleep(2);
                                
                                // ставим статус одобрена и отправляем статус в 1с
                                $this->orders->update_order($order_id, array(
                                    'status'=> 2, 
                                    '1c_id' => $soap_zayavka->return->id_zayavka,
                                    'approve_date' => date('Y-m-d H:i:s'),            
                                ));
                            
                                $tech_manager = $this->managers->get_manager(50);
                                $this->soap->update_status_1c($soap_zayavka->return->id_zayavka, 'Одобрено', $tech_manager->name_1c, $params->payoutAmount, 0, '', 1);
                                
                                // ставим клиенту левел
                                $this->users->update_user($item->user_id, array(
                                    'cdoctor_level' => $params->level,
                                    'cdoctor_pdf' => $params->pdfFile
                                ));

                                $site_id = $this->users->get_site_id_by_user_id($item->user_id);

                                $template = $this->sms->get_template($this->sms::SMS_TEMPLATE_APPROVE_OTHER, $site_id);
                                $message = strtr($template->template, [
                                    '{{amount}}' => $params->payoutAmount,
                                ]);

                        		//отправка смс
                        		$status = $this->smssender->send_sms($old_order->phone_mobile, $message, $site_id);
                        		if($status){
                        			$this->db->query("INSERT INTO sms_log SET phone='".$old_order->phone_mobile."', status='".$status[1]."', dates='".date("Y-m-d H:i:s")."', sms_id='".$status[0]."'");
                        		}

                            }
                        }
                    }
                    
                    // отправляем чек
                    $cdoctor = $this->cdoctor->get_item($item->id);
                    $this->cloudkassir_lagutkin->send_receipt_cdoctor($cdoctor);

            }
        }



        $this->logging(__METHOD__, 'callback', $params, $json, 'cdoctor.txt');


    }
    
}

new CDoctorCallback();