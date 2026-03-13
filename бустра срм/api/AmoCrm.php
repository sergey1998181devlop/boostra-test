<?php

require_once( __DIR__ . '/../api/Simpla.php');

use LPTracker\LPTracker;

class AmoCrm extends Simpla
{
    private $client_id = 'ea438a7d-9303-4b87-8395-8df1e60cd033';
    private $client_secret = 'CnGq1gDZarGzVxS3iQuMTM210aD8hdeQRSWHM1JejqP5pt5smoruoX0hCp1KBtOZ';
    private $code = 'def50200d18b0dd407190db5ad52d09f1fcd87231a5a8517557a0a913c86a9f3633e1dc37795abb02aa75bef5fa383b684360c9c97fb996eac8789ddfc7c3d88f5d0434c813faffd2d8a3c4083e2d1b03e6a7b46cd65beb4fa7d8730a7ff9bf6fb55ab7ee51e802404880210af2920a0a3bf45f87d4c18408a2cb586334435e05346fc2fe7ca500c4099db9266b0a634354009c754167b74cc6e599391784e4f8487dc58c0a2027ee8ffc560f8b3156bd6123334e346f6f7b4211ebeed2cf7da80a836ef16bd47771219ad5f9ff2e789556d7ef295245009a741d23f1a49f4886db1f4ea0866116efdea5d0f9b783556568e9631a1191b25f093fe28a31b0685fe60004f2d4e22811fc92672f37742cffd0479e5e5847c26edc02eee79c59372eed9e3417adfb6ac2ab5240f46c5d9e447b17abb347d9cda068e8fa40950e5349cd479eb4326ee505fe0acb403d3e61c9dd6c3b500b7390a6a4e60a2008b4c966920c97bb44f420a801624091d3ac893cd1704f8dd40409a2e3db1e8db82299dfcdff49e6645c9104046232cd71b5b389cfee9e7a47e413831c139c9edfd001594b8d88f635f67fe2b2bf7fd3f13a79be96b2a127a7c703ac8243ea8';
    private $redirect_uri = 'https://boostra.ru';
    private $subdomain = 'fsspnikolai';
    private $pipeline = 4627207;
    
    public function send_lead($name, $phone) {
        $access_token = $this->refresh_token();

        $curl = curl_init();

        $FIELDS = [
            [
                "name" => "Заявка с boostra.ru",
                "_embedded" => [
                    "contacts" => [
                        [
                            "name" => $name,
                            "custom_fields_values" => [
                                [
                                    "field_code" => "PHONE",
                                    "values" => [
                                        [
                                            "enum_code" => "WORK",
                                            "value" => $phone
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "pipeline_id" => $this->pipeline
            ]
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://{$this->subdomain}.amocrm.ru/api/v4/leads/complex",
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_ENCODING => '',
            //CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($FIELDS),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
                "Authorization: Bearer {$access_token}",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function get_token_from_code($code) {
        $code = !empty($code) ? $code : $this->code;

        $link = 'https://' . $this->subdomain . '.amocrm.ru/oauth2/access_token';

        $data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirect_uri,
        ];

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
        curl_setopt($curl,CURLOPT_URL, $link);
        curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
        curl_setopt($curl,CURLOPT_HEADER, false);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        try
        {
            /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        }
        catch(\Exception $e)
        {
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

        file_put_contents('config/amoTokens.json', $out);
        
        return $out;
    }

    public function refresh_token() {
        $link = 'https://' . $this->subdomain . '.amocrm.ru/oauth2/access_token';

        $data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => json_decode(file_get_contents('config/amoTokens.json'), true)['refresh_token'],
            'redirect_uri' => $this->redirect_uri,
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        //print_r($out);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        try {
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        } catch (\Exception $e) {

            //die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

        $response = json_decode($out, true);

        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];
        $token_type = $response['token_type'];
        $expires_in = $response['expires_in'];

        file_put_contents('config/amoTokens.json', json_encode([
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => $token_type,
            'expires_in' => $expires_in,
        ]));

        return $access_token;
    }

    public function change_step($amo_id, $status_id, $pipeline_id) {
        $access_token = $this->refresh_token();

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://' . $this->subdomain . '.amocrm.ru/api/v4/leads',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS =>'[
    {
        "id": ' . $amo_id .',
        "pipeline_id": ' . $pipeline_id .',
        "status_id": ' . $status_id .',
        "updated_by": 0
    }
]',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json',
                'Cookie: user_lang=ru'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        return $response;
    }
}