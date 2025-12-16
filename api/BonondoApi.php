<?php

require_once( __DIR__ . '/../api/Simpla.php');

class BonondoApi extends Simpla
{
    private $baseUrl;
    private $token;
    private $logLevel;

    public function __construct($postfix)
    {
        parent::__construct();

        $baseUrl = $this->config->bonondo_api_url;
        if (! $baseUrl) {
            throw new LogicException('Bonondo api url not specified');
        }

        $this->db->query("SELECT token FROM application_tokens WHERE `enabled` > 0 AND `app` = 'bonon-{$postfix}'");
        $tokens = $this->db->results('token');
        if (empty($tokens)) {
            throw new LogicException('Bonondo api token not specified');
        }

        $logLevel = $this->config->bonondo_api_log_level ?: 'error';

        $this->baseUrl = $baseUrl;
        $this->logLevel = $logLevel;

        // Выбираем случайный токен для запросов, позволяет разделить идущий в бонон трафик
        mt_srand();
        $this->token = $tokens[mt_rand(1, count($tokens)) - 1];
    }

    /**
     * @param array $params
     * @return array|null
     * @throws Exception
     */
    public function createLoaner($params)
    {
        $uri = 'api/v1/external/loaner/create';

        $payload = [
            'phone'                    => $params['phone'],
            'email'                    => $params['email'],
            'first_name'               => $params['first_name'],
            'patronymic'               => $params['patronymic'],
            'last_name'                => $params['last_name'],
            'birthdate'                => $params['birthdate'],
            'gender'                   => $params['gender'],
            'passport_series'          => $params['passport_series'],
            'passport_number'          => $params['passport_number'],
            'passport_date'            => $params['passport_date'],
            'passport_department_code' => $params['passport_department_code'],
            'birth_place'              => $params['birth_place'],
            'registration_region'      => $params['registration_region'],
            'registration_city'        => $params['registration_city'],
            'registration_street'      => $params['registration_street'],
            'registration_house'       => $params['registration_house'],
            'registration_apartment'   => $params['registration_apartment'],
            'actual_region'            => $params['actual_region'],
            'actual_city'              => $params['actual_city'],
            'actual_street'            => $params['actual_street'],
            'actual_house'             => $params['actual_house'],
            'ractual_apartment'        => $params['ractual_apartment'],
            'timezone'                 => $params['timezone'],
            'amount'                   => $params['amount'],
            'term'                     => $params['term'],
            'scorista'                 => $params['scorista'],
            'education'                => $params['education'],
            'utm_source'               => $params['utm_source'],
            'utm_medium'               => $params['utm_medium'],
            'utm_campaign'             => $params['utm_campaign'],
            'wm_id'                    => $params['wm_id'],
            'click_id'                 => $params['click_id'],
            'guru_id'                  => $params['guru_id'],
            'guru_data'                => $params['guru_data'],
            'referer'                  => $params['referer'],
            'ip'                       => $params['ip'],
            'user_agent'               => $params['user_agent'],
        ];

        list($response, $code) = $this->json($uri, $payload);

        if ($code == 200) {
            return $response['payload'];
        }

        return null;
    }

    /**
     * @param string $uri
     * @param array $payload
     * @return array
     * @throws Exception
     */
    private function json($uri, $payload)
    {
        $url = $this->createUrl($uri);

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "apptoken: $this->token",
            ],
            CURLOPT_HEADER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
        ];
        $curl = curl_init();
        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $data = json_decode($response, true);
        if ($this->logLevel == 'info'
            || ($this->logLevel == 'error' && (! $data || $code > 200))
        ) {
            $this->logging(
                __METHOD__,
                $url,
                $payload,
                "CODE: $code"
                . PHP_EOL . "apptoken: $this->token"
                . PHP_EOL . $response,
               'bonondo_api.txt'
            );
        }
        if (! $data) {
            throw new Exception("Invalid Bonondo api response. Code $code.");
        }

        return [$data, $code];
    }

    /**
     * @param  string $uri
     * @return string
     */
    private function createUrl($uri)
    {
        return "$this->baseUrl/$uri";
    }
}