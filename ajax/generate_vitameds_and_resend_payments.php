<?php

require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/api/addons/TVMedicalApi.php';

error_reporting(E_ERROR);

/**
 * Класс создающий витамеды по экзелю
 * class CronGenerateVitamed
 */
class CronGenerateVitamed extends Simpla
{
    /**
     * @throws Exception
     */
    public function run(): void
    {
        $file = $this->request->files('file');

        if ($file['error'] !== UPLOAD_ERR_OK) {
            exit('Ошибка загрузки файла');
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($fileExtension) !== 'csv') {
            exit('Загружен не CSV файл');
        }


        if (($handle = fopen($file['tmp_name'], "r")) !== false) {
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if ($i++ == 0) {
                    continue;
                } // пропускаем заголовок


                $payment_id = (int)str_replace('PM25-', '', trim($data[1]));
                $amount = (int)str_replace(',', '', trim($data[4]));

                $vitamedPayments = $this->tv_medical->selectPayments(['filter_payment_id' => $payment_id, 'filter_status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS], false);

                if ($vitamedPayments) {
                    var_dump($payment_id . " - exists - " . $vitamedPayments->id);
                    continue;
                }

                $vitamed = $this->tv_medical->getClosestTvMedicalPriceByAmount($amount);
                if (!$vitamed) {
                    var_dump($payment_id . " - doesnt exist vitamed ");
                    continue;
                }
                
                $payment = $this->best2pay->get_payment($payment_id);
                if (!$payment) {
                    var_dump($payment_id . " - doesnt exist payment ");
                    continue;
                }

                $user = $this->users->get_user((int)$payment->user_id);
                if (!$user) {
                    var_dump($payment_id . " - doesnt exist user - " . (int)$payment->user_id);
                    continue;
                }

                $data_tv_medical_payment = [
                    'tv_medical_id' => $vitamed->id,
                    'amount' => $vitamed->price,
                    'user_id' => $payment->user_id,
                    'order_id' => $payment->order_id,
                    'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'payment_id' => $payment_id,
                    'organization_id' => $payment->organization_id,
                    'sent_to_api' => 1
                ];

                $vitamed_payment_id = $this->tv_medical->addPayment($data_tv_medical_payment);

                if (!$vitamed_payment_id) {
                    var_dump($payment_id . " - can not create vitamed - ");
                    continue;
                }

                $payment->tv_medical = (object)$data_tv_medical_payment;

                $this->generateDocs($user, $payment, $vitamed, $vitamed_payment_id);

                $this->best2pay->update_payment($payment_id, ['sent' => 0]);
            }
            fclose($handle);
            echo "Импорт завершён.";
        } else {
            echo "Не удалось открыть CSV.";
        }
    }

    /**
     * @throws Exception
     */
    private function generateDocs($user, $payment, $vitamed, $vitamed_payment_id)
    {
        $tvmed_key = $this->dop_license->createLicenseWithKey(
            $this->dop_license::SERVICE_VITAMED,
            [
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'service_id' => $vitamed_payment_id,
                'organization_id' => $payment->organization_id,
                'amount' => $vitamed->price,
            ]
        );


        $this->tv_medical->generatePayDocs($user, $payment, (int)$payment->order_id, $payment->organization_id, $tvmed_key);
    }

}

$start = microtime(true);
(new CronGenerateVitamed())->run();
$end = microtime(true);

$time_worked = microtime(true) - $start;
exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
