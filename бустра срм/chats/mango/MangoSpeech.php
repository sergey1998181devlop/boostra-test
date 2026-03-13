<?php

namespace chats\mango;

use chats\mango\MangoAccount;
use chats\mango\MangoCurl AS Curl;

class MangoSpeech extends MangoAccount {

    /**
     * Получение тематик разговора (Speech2Text)
     */
    public function getRecordingCategories($data) {
        $url = 'vpbx/queries/recording_categories/';
        $obj = (object) [
                    /*
                     * идентификатор записи разговора
                     */
                    'recording_id' => $data['recordingId']
        ];

        if (isset($data['withTerms'])) {
            /*
             * добавить в результат стоп-слова на которые сработала тематика
             */
            $obj->with_terms = (bool) $data['withTerms'];
        }
        if (isset($data['withNames'])) {
            /*
             * добавить в результат имя тематики из БД
             */
            $obj->with_names = (bool) $data['withNames'];
        }

        return Curl::sendPost($url, $obj);
    }

    /**
     * Получение списка расшифровок распознанных разговоров
     */
    public function getRecordingTranscripts($data) {
        $url = 'queries/recording_transcripts/';
        /*
         * массив идентификаторов записи разговора (не более 500)
         */
        foreach ($array as $id) {
            $obj->recording_id[] = $id;
        }
        return Curl::sendPost($url, $obj);
    }

}
