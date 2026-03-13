<?php

namespace chats\mango;

use chats\mango\MangoAccount;
use chats\mango\MangoCurl AS Curl;
use stdClass;

class MangoSips extends MangoAccount {

    /**
     * Создать sip-учетку
     * 
     * Метод позволяет создать sip учетку для сотрудника. При выборе имени SIP в домене второго
     * уровня, если в Личном кабинете включена функция «API коннектор», происходит подключение
     * услуги «Красивый sip адрес»
     */
    public function createSip($data) {
        $url = 'vpbx/sip/create';
        $obj = new stdClass();
        /*
         * ID пользователя
         */
        $obj->user_id = (int) $data['userId'];
        /*
         * пароль
         */
        $obj->password = (string) $data['password'];
        if (isset($data['login']) AND isset($data['domain'])) {
            /*
             * логин
             */
            $obj->login = (string) $data['login'];
            /*
             * домен
             */
            $obj->domain = (string) $data['domain'];
        }
        if (isset($data['description'])) {
            /*
             * описание
             */
            $obj->description = (string) $data['description'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Редактировать sip-учетку
     */
    public function editSip($data) {
        $url = 'vpbx/sip/update';
        $obj = new stdClass();
        /*
         * SIP-учётки
         */
        $obj->sip_id = (int) $data['sipId'];
        if (isset($data['userId'])) {
            /*
             * ID пользователя
             */
            $obj->user_id = (int) $data['userId'];
        }
        if (isset($data['password'])) {
            /*
             * пароль
             */
            $obj->password = (string) $data['password'];
        }
        if (isset($data['login']) AND isset($data['domain'])) {
            /*
             * логин
             */
            $obj->login = (string) $data['login'];
            /*
             * домен
             */
            $obj->domain = (string) $data['domain'];
        }
        if (isset($data['description'])) {
            /*
             * описание
             */
            $obj->description = (string) $data['description'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Удалить sip-учетку
     */
    public function removeSip($data) {
        $url = 'vpbx/sip/delete';
        $obj = (object) [
                    'sip_id' => (int) $data['sipId']
        ];
        return Curl::sendPost($url, $obj);
    }

}
