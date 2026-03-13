<?php

namespace chats\viber;

use chats\viber\ViberCurl AS Curl;

class ViberUser {

    /**
     * Получить информацию о пользователе по его id в мессенджере
     */
    public function getUserInfo($data) {
        $obj = (object) [
                    'id' => ViberSetDataUser::getUserId($data)
        ];
        return Curl::sendPost('get_user_details', $obj);
    }

    /**
     * Получить статус пользователя по его id в мессенджере (on-line|off-line)
     */
    public function getUserOnLine($data) {
        $obj = (object) [
                    'id' => ViberSetDataUser::getUserId($data)
        ];
        return Curl::sendPost('get_online', $obj);
    }

}
