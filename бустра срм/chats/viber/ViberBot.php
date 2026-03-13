<?php

namespace chats\viber;

use chats\viber\ViberCurl AS Curl;

class ViberBot {

    /**
     * Информация о боте
     */
    public function getInfo() {
        $data = (object) [];
        return Curl::sendPost('get_account_info', $data);
    }

}
