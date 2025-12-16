<?php

/**
 * Класс для работы с Api телемедицины
 * class TVMedicalApi
 */
class TVMedicalApi {

    /**
     * Ключ тестовый для работы с Api
     */
    const API_KEY_TEST = 'eyJhbGciOiJIUzUxMiJ9.eyJleHAiOjE3Njc3OTQ4ODgsInN1YiI6InVzXzJycjNVOSIsInZycyI6MSwicmVmcmVzaCI6ZmFsc2V9.KMcgNQEaweL-pfSJGWtfWajbIE9Px0smD0SFhe9M_faKbSFcUTEhJBGXKnslSRfaIlUZloYYqvZyp63e7EdDXA';


    /**
     * Ключ для работы с Api
     */
    const API_KEY = 'eyJhbGciOiJIUzUxMiJ9.eyJleHAiOjE3Njk2ODIwNjIsInN1YiI6InVzX0VZelBFY2IiLCJ2cnMiOjEsInJlZnJlc2giOmZhbHNlfQ.yr77LhjsJsL0wwjdl8IsT-Wa8hwSMS0BGzeHMHRaKcjqxE0absmJym8uKN3FbMhEN4vX9lq92F1vfQ4AujOiIA';

    /**
     * Тестовый url API
     */
    const TEST_URL_API = 'http://51.250.64.31:8080/externalapi/v1/%s';

    /**
     * Боевой url API
     */
    const URL_API = 'https://lk.doconline.ru/externalapi/v1/%s';

    /**
     * Вызывает метод Api
     * @param string $method
     * @param string $request_type
     * @param array $params
     * @return mixed
     */
    public static function callApi(string $method, string $request_type = 'GET', array $params = [])
    {
        $url = sprintf(self::URL_API, $method);

        $curl_settings = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => FALSE,
            CURLOPT_HEADER => FALSE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . self::API_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE ),
        ];

        if ($request_type === 'POST') {
            $curl_settings[CURLOPT_POST] = TRUE;
        } elseif ($request_type !== 'GET') {
            $curl_settings[CURLOPT_CUSTOMREQUEST] = $request_type;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $curl_settings);

        $response = [];
        $response_request = curl_exec($ch);
        if (curl_errno($ch)) {
            $response['errors'] = curl_error($ch);
        }
        curl_close($ch);
        $response = json_decode($response_request, true);
        $response['success'] = ($response['code'] ?? null) === 'success';

        self::Log($params, $response, $url . " :: " . $request_type);

        return $response;
    }

    /**
     * @param array $data
     * @return array|mixed
     *
     * Параметры:
     * {
        "externalUserId": "10",
        "internalTariffId": 0,
        "needConfirm": true,
        "confirmCode": "string"
        }
     */
    public static function createOrder(array $data)
    {
//        return self::callApi('tariffs/boostra/package', 'POST', $data);
        return ['success' => true];
    }

    /**
     * Логируем запросы
     * @param array $request
     * @param $response
     * @param string $url
     * @return void
     */
    public static function Log(array $request, $response, string $url)
    {
        $log_path = dirname(__DIR__, 2) . '/logs/tv_medical_api_logs.txt';
        ob_start();
        var_dump(compact('request', 'response', 'url'));
        $data = ob_get_clean();

        file_put_contents($log_path, date('c') . PHP_EOL . $data . PHP_EOL, FILE_APPEND);
    }
}
