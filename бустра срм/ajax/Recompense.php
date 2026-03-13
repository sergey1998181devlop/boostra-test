<?php

session_start();

chdir('..');
require 'api/Simpla.php';

class RecompenseAjax extends Simpla
{
    private $manager = NULL;
    
    private $response = [
        'status' => false
    ];
    
    public function __construct()
    {
        parent::__construct();

        if ($this->request->isJson()) {
            if (!$this->request->verifyBearerToken()) {
                $this->response['message'] = 'Invalid or missing token';
                http_response_code(403);
                $this->output();
            }
        }
        
        $this->make_recompense();
    }
    
    private function make_recompense()
    {
        $inputData = $this->request->input();

        $service = $inputData['service'] ?? null;
        $service_id = $inputData['service_id'] ?? null;
        $order_id = $inputData['order_id'] ?? null;
        $return_size = $inputData['return_size'] ?? 'full';
        $manager_id = $inputData['manager_id'] ?? null;

        if (!empty($manager_id)) {
            $this->manager = $this->managers->get_manager(intval($manager_id));
        } elseif (isset($_SESSION['manager_id'])) {
            $this->manager = $this->managers->get_manager(intval($_SESSION['manager_id']));
        }

        if (!$this->manager) {
            $this->response['message'] = 'Менеджер не найден';
            $this->output();
        }

        if (empty($service) || empty($service_id) || empty($order_id)) {
            $this->response['message'] = 'Недостаточно параметров (service, service_id, order_id)';
            $this->output();
        }

        if ($order = $this->orders->get_order($order_id)) {
            if (!empty($order->contract_id))
                $contract = $this->contracts->get_contract($order->contract_id);
            
            $operation_date = date('Y-m-d H:i:s');
            $service_data = $this->get_service_data([
                'service' => $service,
                'service_id' => $service_id,
                'number' => $contract->number,
                'return_size' => $return_size,
            ]);
            
            // возврат
            $return_transaction_id = $this->best2pay->add_transaction([
                'user_id' => $order->user_id,
                'order_id' => $order_id,
                'type' => $service_data['transaction_type'],
                'amount' => $service_data['amount'] * 100,
                'sector' => 0,
                'register_id' => 0,
                'contract_number' => empty($contract) ? '' : $contract->number,
                'reference' => $service_data['reference'],
                'description' => $service_data['description'],
                'created' => $operation_date,
                'operation' => 0,
                'reason_code' => 1,
                'state' => 'APPROVED',
                'body' => '',
                'operation_date' => $operation_date,
                'callback_response' => ' ',
            ]);
            $this->update_service($service_data['service']->id, $service, [
                'return_status' => 2,
                'amount_total_returned' => $service_data['service']->amount_total_returned + $service_data['amount'],
                'return_date' => $operation_date,
                'return_amount' => round($service_data['amount']),
                'return_transaction_id' => $return_transaction_id,
                'return_sent' => 0,
                'return_by_manager_id' => $this->manager->id,
            ]);

            $organization_id = $service_data['service']->organization_id;

            if (in_array(
                $service_data['payment_type'],
                [
                    $this->receipts::PAYMENT_TYPE_CREDIT_DOCTOR,
                    $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR,
                    $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE,
                ],
                true
            )) {
                $organization_id = $this->receipts::ORGANIZATION_FINTEHMARKET;
            }
            
            // чек
            $this->receipts->addItem([
                'user_id' => $order->user_id,
                'order_id' => $order_id,
                'amount' => $service_data['amount'],
                'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                'payment_type' => $service_data['payment_type'],
                'organization_id' => $organization_id,
                'description' => $service_data['receipt_description'],
                'transaction_id' => $return_transaction_id,
            ]);
            
            // comment
            $this->comments->add_comment([
                'manager_id' => $this->manager->id,
                'user_id' => $order->user_id,
                'order_id' => $order_id,
                'block' => 'recompense',
                'text' => $service_data['comment_text'],
                'created' => date('Y-m-d H:i:s'),
            ]);
            $this->soap->send_comment([
                'manager' => $this->manager->name_1c,
                'text' => $service_data['comment_text'],
                'created' => date('Y-m-d H:i:s'),
                'number' => $order->id_1c
            ]);
            
            $this->changelogs->add_changelog([
                'manager_id' => $this->manager->id,
                'created'    => date( 'Y-m-d H:i:s' ),
                'type'       => $service_data['transaction_type'],
                'old_values' => $service_id,
                'new_values' => serialize(  [ 'amount' => $service_data['amount'] ] ),
                'order_id'   => $order_id,
                'user_id'    => $order->user_id,
                'file_id'    => $return_transaction_id,
            ]);

            $this->order_data->set($order_id, $this->order_data::PAYMENT_DEFERMENT);
            
            $this->response['status'] = true;
            $this->response['message'] = 'Возврат успешно проведен.';

        } else {
            $this->response['message'] = 'Заявка не найдена';
        }
        
        $this->output();
    }
    
    private function get_service_data($params)
    {
        switch ($params['service']):
            
            case 'credit_doctor':
                $service_item = $this->credit_doctor->getCreditDoctor($params['service_id']);
                $secondReturnText = (int)$service_item->return_status === 2 ? ' оставшейся части' : '';
                $transaction_type = $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE;
                $payment_type = $service_item->is_penalty ? $this->receipts::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR_CHEQUE : $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE;
                $service_name = $service_item->is_penalty ? 'Кредитный доктор' : 'Финансовый доктор';
                $reference = $service_item->transaction_id;
                $receipt_description = $service_item->is_penalty ? $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR] : $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR];
                $comment_text = 'Возврат' . $secondReturnText . ' Кредитного Доктора от ' . date('d.m.Y', strtotime($service_item->date_added)) . ' (Дата услуги) при выдаче в зачет оплаты займа';
                break;
                
            case 'star_oracle':
                $service_item = $this->star_oracle->getStarOracleById($params['service_id']);
                $secondReturnText = (int)$service_item->return_status === 2 ? ' оставшейся части' : '';
                $transaction_type = $this->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE_CHEQUE;
                $payment_type = $this->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE_CHEQUE;
                $service_name = 'Звездный Оракул';
                $reference = $service_item->transaction_id;
                $receipt_description = $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE];
                $comment_text = 'Возврат' . $secondReturnText . ' Звездный Оракул ' . date('d.m.Y', strtotime($service_item->date_added)) . ' (Дата услуги) при выдаче в зачет оплаты займа';
                break;

            case 'safe_deal':
                $service_item = $this->safe_deal->getById($params['service_id']);
                $secondReturnText = (int)$service_item->return_status === 2 ? ' оставшейся части' : '';
                $transaction_type = $this->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL_CHEQUE;
                $payment_type = $this->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL_CHEQUE;
                $service_name = 'Безопасная сделка';
                $reference = $service_item->transaction_id;
                $receipt_description = $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL];
                $comment_text = 'Возврат' . $secondReturnText . ' Безопасная сделка ' . date('d.m.Y', strtotime($service_item->date_added)) . ' (Дата услуги) при выдаче в зачет оплаты займа';
                break;
                
            case 'multipolis':
                $service_item = $this->multipolis->get_multipolis($params['service_id']);
                $secondReturnText = (int)$service_item->return_status === 2 ? ' оставшейся части' : '';
                $transaction_type = $this->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS_CHEQUE;
                $service_name = 'Консьерж сервис';
                $reference = $service_item->payment_id;
                $receipt_description = $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS];
                $comment_text = 'Возврат' . $secondReturnText . ' Консьерж сервиса от '.date('d.m.Y', strtotime($service_item->date_added)).' (Дата услуги) при продлении в зачет оплаты займа';
                break;
                
            case 'tv_medical':
                $service_item = $this->tv_medical->getPaymentById($params['service_id']);
                $secondReturnText = (int)$service_item->return_status === 2 ? ' оставшейся части' : '';
                $transaction_type = $this->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL_CHEQUE;
                $service_name = 'Телемедицина';
                $reference = $service_item->payment_id;
                $receipt_description = $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL];
                $comment_text = 'Возврат' . $secondReturnText . ' Телемедицины от '.date('d.m.Y', strtotime($service_item->date_added)).' (Дата услуги) при продлении в зачет оплаты займа';
                break;

            default:
                $this->response['message'] = 'Не найден тип возврата: '.$params['service'];
                $this->output();
            
        endswitch;

        $return_size = $params['return_size'];

        $amount_left = $service_item->amount - $service_item->amount_total_returned;

        if ($amount_left <=0) {
            $this->response['message'] = 'Услуга уже возвращена.';
            $this->output();
        }

        if ($return_size === 'half') {
            $amount = round($amount_left / 2);
        } elseif ($return_size === 'seventy_five') {
            $amount = round($amount_left * 0.75);
        }elseif ($return_size === 'twenty_five') {
            $amount = round($amount_left * 0.25);
        } else {
            $amount = $amount_left;
        }
        
        $data = [
            'service' => $service_item,
            'service_name' => $service_name,
            'transaction_type' => $transaction_type,
            'payment_type' => $payment_type ?? $transaction_type,
            'description' => 'Возврат взаимозачетом услуги "'.$service_name.'" по договору '.$params['number'],
            'amount' => $amount,
            'reference' => $reference,
            'receipt_description' => $receipt_description,
            'comment_text' => $comment_text,
        ];

        if ($amount_left < $data['amount']) {
            $this->response['message'] = 'Максимальная сумма возврата: '.$amount_left.' руб';
            $this->output();
        }
        
        return $data;
    }
    
    private function update_service($service_id, $service, $params)
    {
        switch ($service):
            
            case 'credit_doctor':
                $this->credit_doctor->updateUserCreditDoctorData($service_id, $params);
                break;
            case 'star_oracle':
                $this->star_oracle->updateStarOracleData($service_id, $params);
                break;
            case 'safe_deal':
                $this->safe_deal->update($service_id, $params);
                break;
            case 'multipolis':
                $this->multipolis->update_multipolis($service_id, $params);
                break;                
            case 'tv_medical':
                $this->tv_medical->updatePayment($service_id, $params);
                break;
        
        endswitch;
    }
    
    private function output()
    {
        header('Content-type: application/json');
        echo json_encode($this->response);
        exit;
    }
}
new RecompenseAjax();
