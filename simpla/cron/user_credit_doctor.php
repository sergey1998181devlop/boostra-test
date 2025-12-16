<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__DIR__, 2) . '/api/Simpla.php';

/**
 * Class UserCreditDoctorCron
 * Создает автоплатежи для автоматического списания средств
 */
class UserCreditDoctorCron extends Simpla
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run()
    {
        $payments = $this->user_credit_doctor->getEmptyPayments();
        foreach ($payments as $payment) {
            $payment_id = $payment->payment_id;
            $data = [
                'user_id' => (int)$payment->user_id,
                'amount' => $payment->full_amount - $payment->amount,
                'status' => 'new',
                'order_type_id' => $payment->order_type_id,
            ];

            if($order_id = $this->user_credit_doctor->addCDPayment($data))
            {
                // отправляем запрос в yookassa
                $data['id'] = $order_id;
                $data['description'] = 'Оплата услуги Кредитный Доктор (автоплатеж)';
                $data['payment_id'] = $payment_id;

                if ($payment_method_id = $this->yookassa_api->createRecurringPayment($data)) {
                    $this->user_credit_doctor->addCDSavePayment(compact('payment_method_id', 'payment_id'));
                }
            }
        }
    }
}

$object = new UserCreditDoctorCron();
