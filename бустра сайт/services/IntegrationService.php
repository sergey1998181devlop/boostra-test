<?php

namespace services;

use OrderData;
use Orders;
use services\Integration\BankiRu\BankiRu;

final class IntegrationService
{
   public function bankiRuStart(): void
   {
       $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100000;

       $orders = new Orders();
       $banki = BankiRu::getInstance();
       $config = $banki->getConfig();


       $login = $config['dev']['login'];
       $password = $config['dev']['password'];


       $list = $orders->getListOfOrdersWithMoratoriumFlag();

       if (isset($_GET['is_debug'])) {
           echo "<pre>";
           var_dump($list);
           die;
       }

      // try {
         $orderData = new OrderData();
         $loginDto = $banki->auth()->login($login, $password);

         foreach ($list as $order) {
           // added limit
           if (count($list) >= $limit) {
               break;
           }

           $order = (object) $order;
           $phone = substr($order->phone, 1);
           if (!isset($_GET['stop_check_duplicates'])) {
               $check = $banki->checkDuplicate($loginDto->token)->isDuplicateByPhoneNumber($login, $phone);
               if ($check) {
                   continue;
               }
           }

           $response = $banki->leads($loginDto->token)->leads([
               'partnerCode' => $login,
               'firstName' => $order->firstname,
               'requestedAmount' => $order->amount,
               'phoneCountryCode' => (string) $order->phone[0],
               'phoneNumber' => $phone,
               'typeCode' => 4,
               'addressesString' => $order->short . " " . $order->r_city,
               'addressKladrCode' =>  $order->i,
               'acceptedConsents' => [1, 2, 3, 4, 5],
           ]);

           if ($response === false) {
               $orderData->set($order->id, OrderData::SENT_TO_BANKI_RU, 'Dublicate');
               continue;
           }

           //array(1) { [0]=> object(services\Integration\BankiRu\Dto\LeadResponseDto)#17 (4) { ["leadId"]=> int(8760739) ["leadToken"]=> string(40) "EBQfq2klYgtXubedWUTDChLWwr9soAgMEDq4BHbX" ["urlType"]=> NULL ["status"]=> string(7) "success" } }


           $encoded = json_encode($response);
           echo "<br>";
           echo $encoded;
           echo "<br>";

           $orderData->set($order->id, OrderData::SENT_TO_BANKI_RU, $response->leadId ?: 1);
         }

      // } catch (\Throwable $exception) {
      //     throw new \Exception($exception->getMessage());
      // }
   }
}
