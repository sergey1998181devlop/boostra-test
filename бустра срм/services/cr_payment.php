<?php

require_once 'AService.php';

class CreditRatingPayment extends AService
{
    private $action = '';
    public function __construct()
    {
        parent::__construct();
        $this->action = $this->request->get('action');
        $this->run();
    }

    private function run()
    {
        if (method_exists(self::class, $this->action)) {
            $result = $this->{$this->action}();
            $this->response = $result;
        } else {
            $this->response['error'] = 'Method Not exists';
        }

        $this->json_output();
    }


    private function send_payment()
    {
        $payment_id = (int)($this->request->get('payment_id') ?? 0);
        if (!empty($payment_id)) {
            $transaction = $this->transactions->get_payment_id_transaction($payment_id);
            if (!empty($transaction)) {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOplata.1cws?wsdl");

                switch ($transaction->payment_type) {
                    case 'credit_rating':
                        $response_1c = $this->soap->send_credit_rating_payment_result($transaction, $client);
                        break;
                    default:
                        $response_tinkoff = $this->tinkoff->get_state_atop($transaction->payment_id);
                        $response_1c = $this->soap->send_debt_payment_result(
                            $transaction,
                            $response_tinkoff['Status'] == 'AUTHORIZED',
                            $client
                        );
                        $this->transactions->update_transaction($transaction->id, [
                            'sended' => 1,
                            'send_result' => $response_1c->return ?? serialize($response_1c),
                        ]);
                }

                return compact('transaction', 'response_1c');
            } else {
                return ['error' => 'payment_id is not found'];
            }
        } else {
            return ['error' => 'payment_id is empty'];
        }
    }

    private function send_order_payment()
    {
        $order_id = (string)$this->request->get('order_id') ?? 0;
        if (!empty($order_id)) {
            $transaction = $this->transactions->get_order_id_transaction($order_id);
            if (!empty($transaction)) {
                $client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOplata.1cws?wsdl");

                switch ($transaction->payment_type) {
                    case 'credit_rating':
                        $response_1c = $this->soap->send_credit_rating_payment_result($transaction, $client);
                        break;
                    default:
                        $response_tinkoff = $this->tinkoff->get_state_atop($transaction->payment_id);
                        $response_1c = $this->soap->send_debt_payment_result(
                            $transaction,
                            $response_tinkoff['Status'] == 'AUTHORIZED',
                            $client
                        );
                        $this->transactions->update_transaction($transaction->id, [
                            'sended' => 1,
                            'send_result' => $response_1c->return ?? serialize($response_1c),
                        ]);
                }

                return compact('transaction', 'response_1c');
            } else {
                return ['error' => 'order_id is not found'];
            }
        } else {
            return ['error' => 'order_id is empty'];
        }
    }
}

new CreditRatingPayment();
