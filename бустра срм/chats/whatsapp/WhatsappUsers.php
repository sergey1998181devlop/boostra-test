<?php

namespace chats\whatsapp;

use chats\whatsapp\WhatsappCurl AS Curl;
use chats\main\Users;

class WhatsappUsers {

    /**
     * Проверить возможность отправки сообщения в Whatsapp пользователю с указанным номером телефона.
     * 
     * @param type $phone  
     * @return type
     */
    public function checkPhone($data) {
        if (isset($data['phone'])) {
            $data = ['phone' => Users::preparePhone($data['phone'])];
            return Curl::sendGet($data, 'checkPhone');
        }
    }

}
