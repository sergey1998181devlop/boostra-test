<?php

require_once( __DIR__ . '/../vendor/autoload.php');
require_once( __DIR__ . '/../api/Simpla.php');

use LPTracker\LPTracker;

/**
 * @deprecated
 */
class AmoCrm extends Simpla
{
    private $client_id = '133e3d86-ce77-4327-9579-55bdfc8548ab';
    private $client_secret = 'eJlXWP7a5tjbZqIpKh82N2BzvCh2cl23l0ZMsqIKUy01fW1NuEkuihGwXpPSFObm';
    private $code = 'def50200979ab77ed9e1afbc9a895c3d060ae5c758c91393a536cb1e5664432601b1def4fb2a79444e0fcb56d7acefca1749b25c9f09fe9161002b858a12b44340722f9e4b0a21b5578cde4c475bc5d29f4b4579e58229341a5a08a0813a49b7d046b4b8c9844a6c0aff782775825ed609db5f9abb905e2b1e46d0b2c770854deabb4786e67e9b21b0a9f7891d090e8ba95119eb6a45272acdcdc772534a577eba426a30fedf8bfd5f7768193fd17ce1e7c77d2154413794e3cc99d2c3b7409a0732a008a5cb71e3471c38685ca2362c3d493e323d088b16489bd883f7b03ab8554b52bee45bf3dc2b4d92c87933e1b4497f3eb495da791a582247efbc9277888f6bb1e2a6aadba1de1b214a25c3c026a5869afe06462e93002b789427a5bbc1b9950548effd230b6a2b051b5154fb4b00225e6546d738d19854a8e44629f8a7e92e0970485f8d891bf911e4cd29ac9110bed3ce646ae45beb7b1d29d25ea8bc4bd390edae99bb1e3f0fb60e8e6322f2335ca4488e4bf68df5cd76de752368ce302d35dbc72a003a9374e2c0c5cee93a0184215d7507e3d0c46ae63cb122dd6366719fd9d0aecb31cbedef633ba017b0195178bb35151f0b766748474c30d6de';
    private $redirect_uri = 'https://www.boostra.ru';
    private $subdomain = 'prostoprodengiboostra';
    private $pipeline = 4983724;

    // Этап "оформление"
    private $status_arrangement = 44968924;

    public function send_lead($name, $phone, $is_arrangement = false) {
        $access_token = $this->refresh_token();
        if (!$access_token)
        {
            return false;
        }

        $curl = curl_init();

        $FIELDS = [
            [
                "name" => "Заявка на КД с boostra.ru",
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

        if ($is_arrangement)
        {
            $FIELDS[0]["status_id"] = $this->status_arrangement;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://{$this->subdomain}.amocrm.ru/api/v4/leads/complex",
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_ENCODING => '',
            //CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
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
        error_log(__FILE__.':'.__LINE__.': '.var_export($data, true));

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
        catch(Exception $e)
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

        $access_token = "";

        try {
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
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
        } catch (Exception $e) {
            error_log(__FILE__.':'.__LINE__.
                'Error while refreshing token: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

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