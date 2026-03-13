<?php

require_once( __DIR__ . '/../api/Simpla.php');

/**
 * Class AmoCrmApi
 *
 * "expires_in": 86400 - по дефолту access_token жив сутки
 * refresh_token - действует 3 месяца при условии если не было ни одного запроса к Api
 */

class AmoCrmApi extends Simpla
{
    const CLIENT_ID = '93669512-d6ea-4d1f-b0b8-9cdf289c7ea9';
    const CLIENT_SECRET = 'Dpzb37ouOTM9A0LVYR2RyvH4RvdBQLWxDZWNBHN9umHpMnJkJDbXtMYrzQDxG3ur';
    const REDIRECT_URI = 'https://www.boostra.ru';
    const SUBDOMAIN = 'prostoprodengiboostra';

    /**
     * Добавление новой заявки
     * @param $order
     * @return mixed|void
     * @throws Exception
     */
    public function addLid($order)
    {
        $user = $this->users->get_user((int)$order->user_id);

        $lead = [
            [
                'name' => 'Куплен Кредитный Доктор',
                'price' => (int)$order->full_amount,
                "custom_fields_values" => [
                    [
                        "field_id" => 425793, // поле name в сделках
                        "values" => [
                            [
                                "value" => "Тарифный план - $order->order_type_id"
                            ]
                        ]
                    ]
                ],
                "_embedded" => [
                    "contacts" => [
                        [
                            "last_name" => $user->lastname,
                            "first_name" => $user->firstname,
                            "custom_fields_values" => [
                                [
                                    // phone
                                    "field_id" => 421675,
                                    "values" => [
                                        [
                                            "enum_id" => 237291,
                                            "value" => $user->phone_mobile
                                        ]
                                    ]
                                ],
                                [
                                    "field_code" => "EMAIL",
                                    "values" => [
                                        [
                                            "enum_code" => "WORK",
                                            "value" => $user->email
                                        ]
                                    ]
                                ],
                                [
                                    "field_id" => 425789, // NAME ФИО
                                    "values" => [
                                        [
                                            "value" => $this->helpers::getFIO($user),
                                        ]
                                    ]
                                ],
                            ]
                         ]
                    ]
                ],
            ],
        ];

        return $this->sendRequest('/api/v4/leads/complex', $lead);
    }

    /**
     * Отправка запроса
     * @param string $method
     * @param array $data
     * @param string $request_type
     * @param bool $get_token
     * @return mixed|void
     * @throws Exception
     */
    private function sendRequest(string $method, array $data = [], string $request_type = 'POST',  bool $get_token = false)
    {
        $url = 'https://' . self::SUBDOMAIN . '.amocrm.ru' . $method;

        $curl = curl_init();

        if ($get_token) {
            $headers = ['Content-Type: application/json'];
        } else {
            $access_token = $this->refresh_token();
            $headers = [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json',
                'Cookie: user_lang=ru'
            ];
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'amoCRM-oAuth-client/1.0',
            CURLOPT_CUSTOMREQUEST => $request_type,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        /** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        // запишем лог
        //$test = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->logging(__METHOD__, $url, $data, $response, 'amo_crm.txt');

        try
        {
            /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
            if ($code < 200 || $code > 204) {
                throw new Exception($errors[$code] ?? 'Undefined error', $code);
            }
        }
        catch(Exception $e)
        {
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }

        /**
         * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
         * нам придётся перевести ответ в формат, понятный PHP
         */
        return json_decode($response, true);
    }

    /**
     * Получает или обновляет access_token
     * @return mixed|string
     * @throws Exception
     */
    private function refresh_token()
    {
        $access_token = '';

        $db_token = $this->getActualAccessToken();

        // по умолчанию access_token действует 1 сутки или 86400 сек.
        $date_update = new \DateTime($db_token->date_update);

        $date_now = new \DateTime();
        $diffInSeconds = $date_now->getTimestamp() - $date_update->getTimestamp();

        // если токен ещё жив вернем его иначе получим новый
        if ($diffInSeconds < $db_token->expires_in) {
            $access_token = $db_token->access_token;
        } else {
            $response = $this->getNewAccessTokenByRefreshToken($db_token->refresh_token);

            if (!empty($response['access_token'])) {
                $this->updateAccessToken((int)$db_token->id, $response);
                $access_token = $response['access_token'];
            }
        }

        return $access_token;
    }

    /**
     * Получим последнюю запись с токенами
     * @return false|int
     */
    private function getActualAccessToken()
    {
        $query = $this->db->placehold("SELECT * FROM s_amo_tokens ORDER BY id DESC LIMIT 1");
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Обновляет запись в БД
     * @param int $id
     * @param array $data
     * @return void
     */
    private function updateAccessToken(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_amo_tokens SET ?% WHERE id=?", $data, $id);
        $this->db->query($query);
    }

    /**
     * Обмен refresh_token на access_token
     * @param string $refresh_token
     * @return mixed|void
     * @throws Exception
     */
    private function getNewAccessTokenByRefreshToken(string $refresh_token)
    {
        $data = [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'redirect_uri' => self::REDIRECT_URI,
        ];

        return $this->sendRequest('/oauth2/access_token', $data, 'POST', true);
    }
}