<?php

require_once('Simpla.php');

class Cloudkassir_lagutkin extends Simpla
{
    private $ck_API = "85cdc5f03647dd6ff4e10e24e9fe106b";
    private $ck_PublicId = "pk_a02a2d7c066eab6690a2dd0acb32d";
    private $ck_INN = "633013178306";

    public function send_receipt_cdoctor($cdoctor)
    {
    	if (!($user = $this->users->get_user($cdoctor->user_id)))
            return false;
                
        $items = array();
        $item = array(
            'label'           => 'Услуга кредитный рейтинг',
            'price'           => $cdoctor->amount,
            'quantity'        => 1,
            'amount'          => $cdoctor->amount,
            'vat'             => NULL,
            'method'          => 4,
            'object'          => 4,
            'measurementUnit' => 'ед',
        );
        $items[] = $item;
        
        $fio = $user->lastname.' '.$user->firstname.' '.$user->patronymic;
        $receipt = array(
            'Items'         => $items,
            'taxationSystem'=> 0, //система налогообложения; необязательный, если у вас одна система налогообложения
            'customerInfo'     => $fio.', паспорт: '.$user->passport_serial,
            'amounts'       =>array (
                'electronic'     => $cdoctor->amount,
                'advancePayment' => 0,
                'credit'         => 0,
                'provision'      => 0,
            )
        );
//$receipt['email'] = 'kolgotin_vi@akticom.ru';
        if (!empty($user->email))
            $receipt['email'] = $user->email;
        if (!empty($user->phone_mobile))
            $receipt['phone'] = $user->phone_mobile;

        $data = array(
            'Inn'              => $this->ck_INN, //ИНН
            'InvoiceId'        => $cdoctor->id, //номер заказа, необязательный
            'AccountId'        => $user->UID, //идентификатор пользователя, необязательный
            'Type'             => 'Income', //признак расчета
            'CustomerReceipt'  => $receipt,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD,$this->ck_PublicId. ':' . $this->ck_API);
        curl_setopt($ch, CURLOPT_URL, 'https://api.cloudpayments.ru/kkt/receipt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array  (
         'content-type: application/json',
         'X-Request-ID:'.$cdoctor->id.md5(serialize($items)))
        );

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $res = curl_exec($ch);
        curl_close($ch);

        $this->logging(__METHOD__, 'https://api.cloudpayments.ru/kkt/receipt', (array)$data, (array)$res, 'service.log');

        return $res;

    }


    //ответ на запрос
    public  function response($code)
    {
        header('Content-Type:application/json');
        echo json_encode(array('code'=>$code));
    }

}
