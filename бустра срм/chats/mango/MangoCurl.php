<?php

namespace chats\mango;

use chats\mango\MangoSettings AS Settings;
use chats\main\Curl;

class MangoCurl extends Curl {

    public static function sendPost(string $url, object $obj) {
        $url = Settings::mainUrl . $url;
        $curl = self::curlInit($url);
        $data = (object) [
                    'vpbx_api_key' => Settings::apiKey,
                    'json' => json_encode($obj),
                    'sign' => strtolower(hash('sha256', Settings::apiKey . json_encode($obj) . Settings::apiSalt))
        ];
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        return self::curlClose($curl);
    }

}
