<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__) . '/../');
require_once 'api/Simpla.php';

class TinkoffIssuanceCron extends Simpla
{
    public function __construct()
    {
        parent::__construct();

        file_put_contents($this->config->root_dir . 'cron/log.txt', date('d-m-Y H:i:s') . ' Issuance RUN' . PHP_EOL, FILE_APPEND);

        $i = 0;
        while ($i < 5) {
            $this->run();
            $i++;
        }
    }

    private function run()
    {
        $orders = $this->orders->get_orders(
            [
                'id' => 99999999999999999999999999999999,
                'status' => 8,
                'limit' => 1,
            ]
        );

        if ($orders) {
            foreach ($orders as $order) {
                //$amount = intval($order->amount * 100);
                $res = $this->tinkoff->pay_contract($order->order_id);

                if (isset($res['Status']) && $res['Status'] == 'COMPLETED') { //TODO
                    $this->orders->update_order($order->order_id, array('status' => 10));

                    //$this->soap->update_status_1c($order->id_1c, 'Выдан', 'Soap', $order->amount, $order->percent, '', 0, $order->period);

                    // Снимаем страховку если есть
                    if (!empty($order->service_insurance)) {
                        // определяем на какое ип страховка
                        $insurer = $this->orders->get_insure_ip();
                        
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

                        $contract_number = 'Б' . date('y', strtotime($order->date)) . '-';
                        if ($order->order_id > 999999)
                            $contract_number .= $order->order_id;
                        else
                            $contract_number .= '0' . $order->order_id;

                        //$fio = $order->lastname . ' ' . $order->firstname . ' ' . $order->patronymic;
                        //$description = 'Страховой полис к договору ' . $contract_number . ' ' . $fio;

                        $insurance_amount = $insurance_summ * 100;

                        //$response = $this->best2pay->recurrent($order->card_id, $insurance_amount, $description);
                        //$response = $this->best2pay->purchase_by_token($order->card_id, $insurance_amount, $description);

                        $rebill_id = '';

                        $card_list = $this->notify->soap_get_card_list($order->uid);

                        if ($card_list) {
                            foreach ($card_list as $card) {
                                if ($card->Status == 'A' && $card->CardId == $order->card_id) {
                                    $rebill_id = $card->RebillId;
                                }
                            };
                        };

                        $response = $this->tinkoff->take_insurance($order, $order->card_id, $rebill_id, $insurance_amount, $contract_number);

                        $status = $response['Status'];

                        if ($status == 'CONFIRMED') {
                            $transaction = $this->transactions->get_payment_id_transaction($response['PaymentId']);

                            $insure_id = $this->tinkoff->add_insure(array(
                                'amount' => $insurance_summ,
                                'p2pcredit_id' => empty($p2pcredit) ? 0 : $p2pcredit->id,
                                'transaction_id' => $transaction->id,
                                'user_id' => $transaction->user_id,
                                'order_id' => $order->order_id,
                                'date' => date('Y-m-d H:i:s'),
                                'register_id' => $response['PaymentId'],
                                'operation_id' => $response['OrderId'],
                                'response' => serialize($response),
                                'status' => 0,
                            ));

                            //Отправляем чек по страховке
                            //$this->cloudkassir->send_insurance($operation_id);

                            return true;
                        } else {

                        }
                    }
                } else {
                    $this->orders->update_order($order->order_id, ['status' => 11, 'pay_result' => $res]); // статус 11 - не удалось выдать

                    //if ($order = $this->orders->get_order((int)$contract->order_id))
                    //{
                    //    $this->soap1c->send_order_status($order->id_1c, 'Отказано');
                    //}
                }

                //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($res);echo '</pre><hr />';                
                //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order);echo '</pre><hr />';                
            }
        }
    }
}

$cron = new TinkoffIssuanceCron();
