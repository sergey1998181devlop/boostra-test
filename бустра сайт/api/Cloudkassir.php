<?php

require_once('Simpla.php');
require_once 'Helpers.php';

/**
 * Класс для отправки чеков
 *
 * class Cloudkassir
 * https://developers.cloudkassir.ru/#items
 */
class Cloudkassir extends Simpla
{
    /**
     * Приход
     */
    public const TYPE_INCOME = 'Income';

    /**
     * Возврат прихода
     */
    public const TYPE_INCOME_RETURN = 'IncomeReturn';

    /**
     * Отправляет чеки на сервер
     * @param $input_data
     * @return false|mixed
     */
    public function send_receipt($input_data)
    {
        if (!($user = $this->users->get_user((int)$input_data->user_id))) {
            return false;
        }

        $receipt_type = in_array($input_data->payment_type, $this->receipts::getIncomeTypes()) ? self::TYPE_INCOME : self::TYPE_INCOME_RETURN; // приход или возврат

        $organization = $this->organizations->get_organization($input_data->organization_id);

        $items = [];
        $total_amount = 0;

        foreach ($input_data->Services as $service) {
            $service_price = $service->amount;
            $item = [
                'label' => $service->description,
                'price' => $service_price,
                'quantity' => 1,
                'amount' => $service_price,
                'method' => 4,
                'object' => 4,
                'vat' => $organization->params['vat'] ?? null,
                'measurementUnit' => 'ед',
                'AgentData' => null,
            ];

            $total_amount += $service_price;

            /* if ($receipt_type === self::TYPE_INCOME) {
                  switch ($service->payment_type) {
                        case $this->receipts::PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR:
                            $item['AgentSign'] = 6;
                            $item['PurveyorData'] = $this->getPurveyorData($this->config->RECEIPT['credit_doctor_organization_id']);
                            break;
                        case $this->receipts::PAYMENT_TYPE_TV_MEDICAL:
                        case $this->receipts::PAYMENT_TYPE_MULTIPOLIS:
                            $item['AgentSign'] = 6;
                            $item['PurveyorData'] = $this->getPurveyorData($this->config->RECEIPT['tv_medical_organization_id']);
                            break;
                }
                $item['AgentSign'] = 6;
            }*/

            $items[] = $item;
        }

        $receipt = [
            'Items' => $items,
            'taxationSystem' => $organization->params['taxationSystem'] ?? 0, //система налогообложения; необязательный, если у вас одна система налогообложения
            'customerInfo' => self::getCustomerReceipt($user),
            'amounts' => [
                'electronic' => $total_amount,
                'advancePayment' => 0,
                'credit' => 0,
                'provision' => 0,
            ],
        ];

        //убрал отправку чека на почту юзера
//        if (!empty($user->email)) {
//            $receipt['email'] = $user->email;
//        }

//        if (!empty($user->phone_mobile)) {
//            $receipt['phone'] = $user->phone_mobile;
//        }

        $data = [
            'Inn' => $organization->inn, //ИНН
            'InvoiceId' => $input_data->id, //номер заказа, необязательный
            'AccountId' => $user->id, //идентификатор пользователя, необязательный
            'Type' => $receipt_type, //признак расчета
            'CustomerReceipt' => $receipt,
        ];

        return $this->sendRequest($data, $input_data->id, $items, $organization->params);
    }

    /**
     * Генерирует данные пользователя ФИО + Паспорт
     * @param $user
     * @return string
     */
    public static function getCustomerReceipt($user): string
    {
        $clear_passport_serial = preg_replace('/[^0-9]/', '', $user->passport_serial);
        $passport_serial = substr($clear_passport_serial, 0, 4);
        $passport_number = substr($clear_passport_serial, 4);
        $fio = Helpers::getFIO($user);

        return $fio . ', паспорт: ' . $passport_serial . ' ' . $passport_number;
    }


    /**
     * Получает данные об организации
     * @param int $organization_id
     * @return array
     */
    public function getPurveyorData(int $organization_id): array
    {
        $organization = $this->organizations->get_organization($organization_id);
        return [
            'Phone' => $organization->phone,
            'Name' => $organization->name,
            'Inn' => $organization->inn,
        ];
    }

    /**
     * Отправляет запрос на сервер
     * @param array $data
     * @param int $receipt_id
     * @param array $items
     * @param array $keys
     * @return mixed
     */
    private function sendRequest(array $data, int $receipt_id, array $items, array $keys = [])
    {

        $ck_PublicId = $keys['ck_publicid'];
        $ck_API = $keys['ck_api'];

        $headers = [
            'content-type: application/json',
            'X-Request-ID:' . $receipt_id . md5(serialize($items))
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $ck_PublicId . ':' . $ck_API);
        curl_setopt($ch, CURLOPT_URL, $this->config->RECEIPT['url']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $res = curl_exec($ch);
        curl_close($ch);

        $this->logging(__METHOD__, $this->config->RECEIPT['url'], $data, (array)$res, 'cloudkassir_receipt.log');

        return json_decode($res, true);
    }
}
