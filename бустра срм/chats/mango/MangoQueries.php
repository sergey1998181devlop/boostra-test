<?php

namespace chats\mango;

use chats\mango\MangoCurl AS Curl;
use chats\mango\MangoAccount;

class MangoQueries extends MangoAccount {

    /**
     * Запрос истории навигации посетителя сайта по динамическому номеру
     */
    public function getUserHistoryDct($data) {
        $url = 'vpbx/queries/user_history_by_dct_number/';
        $obj = (object) [
                    /*
                     * динамический номер
                     */
                    'number' => (string) $data['number'],
                    'data' => (array) [
                        /*
                         * абсолютный адрес страницы
                         */
                        'url' => (string) $data['url'],
                        /*
                         * дата и время открытия страницы в формате UTC+3 (по ISO)
                         */
                        'date' => (string) $data['date']
                    ]
        ];
        if (isset($data['title'])) {
            /*
             * строка - заголовок страницы
             */
            $obj->data['title'] = (string) $data['title'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Запрос информации о посетителе сайта по динамическому номеру
     */
    public function getUserInfoDct($data) {
        $url = 'vpbx/queries/user_info_by_dct_number/';
        $obj = (object) [
                    'number' => (string) $data['number']
        ];
        return Curl::sendPost($url, $obj);
    }

}
