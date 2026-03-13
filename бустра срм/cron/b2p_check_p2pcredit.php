<?php

chdir(dirname(__FILE__).'/../');
require_once 'api/Simpla.php';
require_once 'api/Helpers.php';

$simpla = new Simpla();

$simpla->db->query("
    SELECT *
    FROM b2p_p2pcredits
    WHERE operation_id = ''
    AND status != 'REJECTED'
");
$results = $simpla->db->results();
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';
foreach ($results as $p2pcredit)
{
    $response = $simpla->best2pay->get_register_info(8098, $p2pcredit->register_id, $get_token = 1);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';    
    $xml = simplexml_load_string($response);
    $status = (string)$xml->state;
    
    if ($status == 'REGISTERED')
    {
        foreach ($xml->operations as $xml_operation)
            if ($xml_operation->operation->state == 'REJECTED')
                $operation = (string)$xml_operation->operation->id;
        
        if (!empty($operation))
        {
            $simpla->best2pay->update_p2pcredit($p2pcredit->id, array(
                'response' => $response, 
                'status' => 'REJECTED',
                'operation_id' => $operation,
                'complete_date' => date('Y-m-d H:i:s'),
            ));
        
            $simpla->orders->update_order($p2pcredit->order_id, array('status'=>11));
        }
    }
    elseif ($status == 'COMPLETED')
    {
        foreach ($xml->operations as $xml_operation)
            if ($xml_operation->operation->state == 'APPROVED')
                $operation = (string)$xml_operation->operation->id;

        if (!empty($operation))
        {
            $order = $simpla->orders->get_order((int)$p2pcredit->order_id);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order);echo '</pre><hr />';            
//exit;

            
            $simpla->best2pay->update_p2pcredit($p2pcredit->id, array(
                'response' => $response, 
                'status' => 'APPROVED',
                'operation_id' => $operation,
                'complete_date' => date('Y-m-d H:i:s'),
            ));


            $order_id = $order->order_id;
            $contract_number = 'Б' . date('y', strtotime($order->date)) . '-';
            if ($order->order_id > 999999) {
                $contract_number .= $order->order_id;
            } else {
                $contract_number .= '0' . $order->order_id;
            }

            $simpla->orders->update_order($p2pcredit->order_id, array('status'=>10));
                                
//                    $this->soap->update_status_1c($order->id_1c, 'Выдан', 'Soap', $order->amount, $order->percent, '', 0, $order->period);
            
            // Снимаем страховку если есть
            if (!empty($order->service_insurance))
            {
                if ($order->percent == 0)
                    $insure = 0.33;
                elseif ($order->amount <= 2000)
                    $insure = 0.23;
                elseif ($order->amount <= 4000)
                    $insure = 0.18;
                elseif ($order->amount <= 7000)
                    $insure = 0.15;
                elseif ($order->amount <= 10000)
                    $insure = 0.14;
                else
                    $insure = 0.13;
                
                $insurance_summ = round($order->amount * $insure, 2);
                
                $fio = $order->lastname.' '.$order->firstname.' '.$order->patronymic;
                
                $description = 'Страховой полис к договору '.$contract_number.' '.$fio;
                
                if ($order->user_id == 88449)
                {
                    $insurance_summ = 7500;
                }
                
                $insurance_amount = $insurance_summ * 100;
                $response = $simpla->best2pay->purchase_by_token($order->card_id, $insurance_amount, $description, false, compact('contract_number', 'order_id'));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';                
                $xml = simplexml_load_string($response);
                $status = (string)$xml->state;
        
                if ($status == 'APPROVED')
                {
                    
                    $register_id = (string)$xml->order_id; 
                    $operation_id = (string)$xml->id;
                    
                    $transaction = $simpla->best2pay->get_register_id_transaction($register_id, $operation_id);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($register_id, $operation_id, $transaction);echo '</pre><hr />';                
                                                
                    $insure_id = $simpla->best2pay->add_insure(array(
                        'amount' => $insurance_summ,
                        'p2pcredit_id' => empty($p2pcredit) ? 0 : $p2pcredit->id,
                        'transaction_id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'order_id' => $p2pcredit->order_id,
                        'date' => date('Y-m-d H:i:s'),
                        'register_id' => $xml->order_id,
                        'operation_id' => $xml->id,
                        'response' => serialize($response),
                        'status' => 'APPROVED',
                    ));
                    

                    
                    //Отправляем чек по страховке
//                            $simpla->cloudkassir->send_insurance($operation_id);
                    
                    //return true;
                    
                }
                else
                {
                    
                }
            }


            if ($order->is_user_credit_doctor == 1) {
                $credit_doctor = $simpla->credit_doctor->getUserCreditDoctor((int)$order->order_id, (int)$order->user_id);
                if (!empty($credit_doctor)) {
                    $fio = Helpers::getFIO($order);
                    $cd_description = "Кредитный доктор - $credit_doctor->credit_doctor_condition_id к заявке $order->order_id $fio";
                    $cd_amount = $credit_doctor->amount * 100;
                    $response = $simpla->best2pay->purchase_by_token($order->card_id, $cd_amount, $cd_description, true, compact('contract_number', 'order_id'));

                    $xml = simplexml_load_string($response);
                    $status = (string)$xml->state;

                    if ($status === $simpla->best2pay::STATUS_APPROVED)
                    {
                        $register_id = (string)$xml->order_id;
                        $operation_id = (string)$xml->id;
                        $transaction = $simpla->best2pay->get_register_id_transaction($register_id, $operation_id);

                        // добавим задание на отправку чека
                        $receipt_data = [
                            'user_id' => $order->user_id,
                            'order_id' => $order->order_id,
                            'amount' => $credit_doctor->amount,
                            'transaction_id' => $transaction->id,
                            'payment_method' => $simpla->orders::PAYMENT_METHOD_B2P,
                            'payment_type' => $simpla->receipts::PAYMENT_TYPE_CREDIT_DOCTOR,
                            'organization_id' => $simpla->receipts::ORGANIZATION_BOOSTRA,
                            'description' => $simpla->receipts::PAYMENT_DESCRIPTIONS[$simpla->receipts::PAYMENT_TYPE_CREDIT_DOCTOR],
                        ];

                        $simpla->receipts->addItem($receipt_data);

                        // проставляем оплату КД
                        $simpla->credit_doctor->updateUserCreditDoctorData(
                            $credit_doctor->id,
                            [
                                'status' => $simpla->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
                                'transaction_id' => $transaction->id,
                            ]
                        );

                        // генерируем документ
                        $simpla->credit_doctor->createDocument($order, (int)$credit_doctor->amount);
                    }
                }
            }

            
        }

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($operation);echo '</pre><hr />';    
        
        
//        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($p2pcredit, $response, $status);echo '</pre><hr />';
    }
}

