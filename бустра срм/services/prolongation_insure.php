<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require_once 'AService.php';

class ProlongationInsureService extends AService
{
    public function __construct()
    {
    	parent::__construct();
                
        $this->run();
    }
    
    private function run()
    {
        $this->logging('', __FILE__, $_GET, $_POST, 'insure.txt');
        
        $payment_id = $this->request->get('payment_id');
        $number = $this->request->get('number');
        
        if (!empty($payment_id))
        {
            if ($transaction = $this->transactions->get_payment_id_transaction($payment_id))
            {
                $this->response['success'] = 1;
                
                $insurance_period = $this->insurances->get_insurance_period();
                $start_date = date("Y-m-d 00:00:00", time() + 86400);
                $end_date = date("Y-m-d 00:00:00", time() + (1 + $insurance_period) * 86400);
                
                if (!empty($transaction->loan_id))
                    $order_id = $this->orders->get_order_1cid($transaction->loan_id);
                
                $insurer = $this->insurances->get_insurer($number);
                
                $insurance_id = $this->insurances->add_insurance(array(
                    'number' => $number,
                    'amount' => $transaction->insure_amount,
                    'user_id' => $transaction->user_id,
                    'order_id' => empty($order_id) ? 0 : (int)$order_id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'transaction_id' => $transaction->id,
                    'contract_number' => $transaction->contract_number,
                    'insurer' => $transaction->insurer,
                ));
                
                $this->insurances->create_insurance_documents($insurance_id);
                
            }
            else
            {
                $this->response['error'] = 'TRANSACTION NOT FOUND';
            }
        }
        else
        {                
            $this->response['error'] = 'UNDEFINED payment_id';
        }

        $this->json_output();
    }
}
new ProlongationInsureService();