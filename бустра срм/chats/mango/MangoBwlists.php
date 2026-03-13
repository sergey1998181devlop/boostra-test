<?php

namespace chats\mango;

use chats\mango\MangoAccount;
use chats\mango\MangoCurl AS Curl;
use stdClass;

class MangoBwlists extends MangoAccount {

    /**
     * Добавить номер в ч/б список
     */
    public function addNumberInList($data) {
        $url = 'vpbx/bwlists/number/add/';
        $obj = new stdClass();
        /*
         * тип списка
         */
        $obj->list_type = $data['listType'];
        /*
         * номер. Может быть указана маска. "*" - означает произвольную 
         * последовательность цифр/символов, "#" - означает одну произвольную
         * цифру/символ. Кроме того, могут быть заданы диапазоны номеров,
         * используя тире "-" в качестве разделителя
         */
        $obj->number['number'] = $data['number'];
        /*
         * тип номера, "tel", "sip"
         */
        $obj->number['number_type'] = $data['numberType'];
        if (isset($data['comment'])) {
            /*
             * комментарий, до 255 символов
             */
            $obj->number['comment'] = $data['comment'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Удаление номера из ч/б списка
     */
    public function removeNumberFromList($data) {
        $url = 'vpbx/bwlists/number/delete/';
        $obj = (object) [
                    /*
                     * id номера
                     */
                    'number_id' => $data['numberId']
        ];
        return Curl::sendPost($url, $obj);
    }

}
