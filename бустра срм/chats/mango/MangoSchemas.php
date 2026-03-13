<?php

namespace chats\mango;

use chats\mango\MangoCurl AS Curl;
use chats\mango\MangoAccount;
use stdClass;

class MangoSchemas extends MangoAccount {

    /**
     * Получение списка схем переадресаций
     */
    public function getSchemas($data) {
        $url = 'vpbx/schemas/';
        $obj = new stdClass;
        if (isset($data['trunksNumbers'])) {
            /*
             * номер sip-trunk'а
             */
            $obj->trunks_numbers = (string) $data['trunksNumbers'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Установить схему на входящий номер
     */
    public function setSchemaByNumber($data) {
        $url = 'vpbx/schema/set/';
        $obj = (object) [
                    /*
                     * id схемы, можно получить запросом списка схем
                     */
                    'schema_id' => $data['schemaId'],
                    /*
                     * id линии, можно получить запросом списка номеров
                     */
                    'line_id' => $data['lineId']
        ];
        if (isset($data['trunkNumberId'])) {
            $obj->trunk_number_id = (int) $data['trunkNumberId'];
        }
        return Curl::sendPost($url, $obj);
    }

}
