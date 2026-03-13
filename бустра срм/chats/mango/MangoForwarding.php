<?php

namespace chats\mango;

use chats\mango\MangoAccount;
use chats\mango\MangoCurl AS Curl;

class MangoForwarding extends MangoAccount {

    /**
     * Добавление нового правила переадресации
     */
    public function forvardAdd($data) {
        $url = 'vpbx/forwarding/number/add';
        if (!$data['clientPhoneType']) {
            $phone = $data['clientPhoneNumberDid'];
        } else {
            $phone = $data['clientPhoneNumberSip'];
        }

        # Обязательные параметры 

        $obj = (object) [
                    /*
                     * номер, с которого поступает входящий звонок
                     */
                    'client_phone_number' => (string) $phone,
                    /*
                     * тип номера, с которого поступает входящий звонок (0 - телефон, 1 - SIP-Номер) 
                     */
                    'client_phone_type' => (bool) $data['clientPhoneType'],
                    /*
                     * статус активности правила переадресации (0 - правило неактивно, 1 - правило активно)
                     */
                    'status' => (bool) $data['forvardStatus'],
                    /*
                     * тип переадресации (group - группа, user - сотрудник, ext_forward - внешний номер)
                     */
                    'forward_type' => (string) $data['forvardType'],
        ];

        # Опциональные параметры

        /*
         * ID сотрудника, на которого осуществляется переадресация
         */
        if (isset($data['forvardGroupId'])) {
            $obj->forward_to_group['forward_group_id'] = (int) $data['forvardGroupId'];
        }
        if (isset($data['forward_user_id'])) {
            /*
             * ID сотрудника, на которого осуществляется переадресация
             */
            $obj->forward_to_user['forward_user_id'] = (int) $data['forward_user_id'];
        }
        if (isset($data['forward_contact_id'])) {
            /*
             * ID контакта сотрудника, на который осуществляется переадресация, 
             * если передана пустая строка, то устанавливается номер по умолчанию
             */
            $obj->forvard_to_user['forward_contact_id'] = (string) $data['forwardContactId']; #
        }
        if (isset($data['forwardNumberType'])) {
            /*
             * тип внешнего номера, на который осуществляется переадресация 
             * вызова (0 - телефон, 1 - SIP-Номер)
             */
            $obj->forward_to_ext['forward_number_type'] = (bool) $data['forwardNumberType'];
        }
        if (isset($data['forwardNumber'])) {
            /*
             * внешний номер, на который осуществляется переадресация вызова
             */
            $obj->forvard_to_ext['forward_number'] = (string) $data['forwardNumber'];
        }
        if (isset($data['forwardWaitSec'])) {
            /*
             * время ожидания ответа абонента (в секундах) при переадресации на внешний номер
             */
            $obj->forvard_to_ext['forward_wait_sec'] = (int) $data['forwardWaitSec'];
        }
        if (isset($data['comment'])) {
            /*
             * комментарий
             */
            $obj->comment = (string) $data['comment'];
        }

        return Curl::sendPost($url, $obj);
    }

    /**
     * Изменение правила переадресации
     */
    public function forvardChange($data) {
        $url = 'vpbx/forwarding/number/change/';

        # Обязательные параметры

        $obj = (object) [
                    'forward_id' => (int) $data['forvardId']
        ];

        # Опциональные параметры

        /*
         * тип переадресации (group - группа, user - сотрудник, ext_forward - внешний номер)
         */
        if (isset($data['forwardtype'])) {
            $obj->forward_type = $data['forwardtype'];
        }

        /*
         * ID сотрудника, на которого осуществляется переадресация
         */
        if (isset($data['forvardGroupId'])) {
            $obj->forward_to_group['forward_group_id'] = (int) $data['forvardGroupId'];
        }
        if (isset($data['forward_user_id'])) {
            /*
             * ID сотрудника, на которого осуществляется переадресация
             */
            $obj->forward_to_user['forward_user_id'] = (int) $data['forward_user_id'];
        }
        if (isset($data['forward_contact_id'])) {
            /*
             * ID контакта сотрудника, на который осуществляется переадресация, 
             * если передана пустая строка, то устанавливается номер по умолчанию
             */
            $obj->forvard_to_user['forward_contact_id'] = (string) $data['forwardContactId']; #
        }
        if (isset($data['forwardNumberType'])) {
            /*
             * тип внешнего номера, на который осуществляется переадресация 
             * вызова (0 - телефон, 1 - SIP-Номер)
             */
            $obj->forward_to_ext['forward_number_type'] = (bool) $data['forwardNumberType'];
        }
        if (isset($data['forwardNumber'])) {
            /*
             * внешний номер, на который осуществляется переадресация вызова
             */
            $obj->forvard_to_ext['forward_number'] = (string) $data['forwardNumber'];
        }
        if (isset($data['forwardWaitSec'])) {
            /*
             * время ожидания ответа абонента (в секундах) при переадресации на внешний номер
             */
            $obj->forvard_to_ext['forward_wait_sec'] = (int) $data['forwardWaitSec'];
        }
        if (isset($data['comment'])) {
            /*
             * комментарий
             */
            $obj->comment = (string) $data['comment'];
        }
        /*
         * тип номера, с которого поступает входящий звонок (0 - телефон, 1 - SIP-Номер) 
         * если задан параметр client_phone_number, то client_phone_type является обязательным
         */
        if (isset($data['clientPhoneType'])) {
            if (!$data['clientPhoneType']) {
                $phone = $data['clientPhoneNumberDid'];
            } else {
                $phone = $data['clientPhoneNumberSip'];
                /*
                 * номер, с которого поступает входящий звонок;
                 * указанный номер должен соответствовать типу номера указанного 
                 * в client_phone_type (если client_phone_type = 0, 
                 * тогда client_phone_number - DID-номер; 
                 * если client_phone_type = 1, тогда client_phone_number - SIP-номер)
                 */
                $obj->client_phone_number = $phone;
                /*
                 * тип номера, с которого поступает входящий звонок 
                 * (0 - телефон, 1 - SIP-Номер);
                 * если задан параметр client_phone_number, то client_phone_type является обязательным
                 */
                $obj->client_phone_type = $data['clientPhoneType'];
            }
        }
        if (isset($data['status'])) {
            /*
             * статус активности правила переадресации (0 - правило неактивно,1 - правило активно)
             */
            $obj->status = (bool) $data['status'];
        }

        return Curl::sendPost($url, $obj);
    }

    /**
     * Удаление правила переадресации
     */
    public function forvardRemove($data) {
        $url = 'vpbx/forwarding/number/remove/';
        $obj = (object) [
                    'forward_id' => (int) $data['forwardId']
        ];
        return Curl::sendPost($url, $obj);
    }

}
