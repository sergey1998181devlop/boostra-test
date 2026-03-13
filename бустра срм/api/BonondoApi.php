<?php

require_once( __DIR__ . '/../api/Simpla.php');

class BonondoApi extends Simpla
{
    private $baseUrl;
    private $logLevel;

    public function __construct()
    {
        parent::__construct();

        $this->baseUrl = $this->settings->bonon_api_url;
        $this->logLevel = $this->settings->bonondo_api_log_level ?: 'error';

        if (!$this->baseUrl) {
            throw new LogicException('Bonondo api url not specified');
        }
    }

    /**
     * @param array $params
     * @return array|null
     * @throws Exception
     */
    public function sendShortApi($params, $site_id)
    {
        $this->db->query("SELECT token FROM application_tokens WHERE `enabled` > 0 AND `app` = 'bonon_short_api' AND site_id = ?", $site_id);
        $token = $this->db->result('token');
        $path  = $this->settings->bonon_short_api_path;
        if (empty($token)) {
            throw new LogicException('Bonondo api token not specified');
        }

        $payload = [
            'phone'                    => $params['phone'],
            'email'                    => $params['email'],
            'first_name'               => $params['first_name'],
            'patronymic'               => $params['patronymic'],
            'last_name'                => $params['last_name'],
            'birthdate'                => $params['birthdate'],
            'passport_series'          => $params['passport_series'],
            'passport_number'          => $params['passport_number'],
            'passport_date'            => $params['passport_date'],
            'passport_department_code' => $params['passport_department_code'],
            'birth_place'              => $params['birth_place'],
            'gender'                   => $params['gender'],
            'registration_region'      => $params['registration_region'],
            'registration_city'        => $params['registration_city'],
            'registration_street'      => $params['registration_street'],
            'registration_house'       => $params['registration_house'],
            'registration_apartment'   => $params['registration_apartment'],
            'amount'                   => $params['amount'],
            'term'                     => $params['term'],
            'scorista'                 => $params['scorista'],
            'utm_source'               => $params['utm_source'],
            'utm_medium'               => $params['utm_medium'],
            'utm_campaign'             => $params['utm_campaign'],
            'wm_id'                    => $params['wm_id'],
            'click_id'                 => $params['click_id'],
            'juicy_id'                 => $params['juicy_id'],
        ];

        list($response, $code) = $this->json($path, $payload, $token);

        return $code == 200 ? $response : null;
    }

    /**
     * @param array $params
     * @param string $site_id
     * @return array|null
     * @throws Exception
     */
    public function sendIssuedLoan($params, $site_id)
    {
        $this->db->query("SELECT token FROM application_tokens WHERE `enabled` > 0 AND `app` = 'bonon_issued_loans' AND site_id = ?", $site_id);
        $token = $this->db->result('token');
        $path  = $this->settings->bonon_issued_loans_path;
        if (empty($token)) {
            throw new LogicException('Bonondo api token not specified');
        }

        $payload = [
            'phone'                    => $params['phone'],
            'email'                    => $params['email'],
            'first_name'               => $params['first_name'],
            'patronymic'               => $params['patronymic'],
            'last_name'                => $params['last_name'],
            'birthdate'                => $params['birthdate'],
            'passport_series'          => $params['passport_series'],
            'passport_number'          => $params['passport_number'],
            'passport_date'            => $params['passport_date'],
            'passport_department_code' => $params['passport_department_code'],
            'birth_place'              => $params['birth_place'],
            'gender'                   => $params['gender'],
            'registration_region'      => $params['registration_region'],
            'registration_city'        => $params['registration_city'],
            'registration_street'      => $params['registration_street'],
            'registration_house'       => $params['registration_house'],
            'registration_apartment'   => $params['registration_apartment'],
            'amount'                   => $params['amount'],
            'term'                     => $params['term'],
            'scorista'                 => $params['scorista'],
            'utm_source'               => $params['utm_source'],
            'utm_medium'               => $params['utm_medium'],
            'utm_campaign'             => $params['utm_campaign'],
            'wm_id'                    => $params['wm_id'],
            'click_id'                 => $params['click_id'],
            'juicy_id'                 => $params['juicy_id'],
        ];

        list($response, $code) = $this->json($path, $payload, $token);

        return $code == 200 ? $response : null;
    }

    /**
     * @param array $params
     * @param string $site_id
     * @return array|null
     * @throws Exception
     */
    public function sendUpcomingPayments($params, $site_id)
    {
        $this->db->query("SELECT token FROM application_tokens WHERE `enabled` > 0 AND `app` = 'bonon_upcoming_payments' AND site_id = ?", $site_id);
        $token = $this->db->result('token');
        $path  = $this->settings->bonon_upcoming_payments_path;
        if (empty($token)) {
            throw new LogicException('Bonondo api token not specified');
        }

        $payload = [
            'phone'                    => $params['phone'],
            'email'                    => $params['email'],
            'first_name'               => $params['first_name'],
            'patronymic'               => $params['patronymic'],
            'last_name'                => $params['last_name'],
            'birthdate'                => $params['birthdate'],
            'passport_series'          => $params['passport_series'],
            'passport_number'          => $params['passport_number'],
            'passport_date'            => $params['passport_date'],
            'passport_department_code' => $params['passport_department_code'],
            'birth_place'              => $params['birth_place'],
            'gender'                   => $params['gender'],
            'registration_region'      => $params['registration_region'],
            'registration_city'        => $params['registration_city'],
            'registration_street'      => $params['registration_street'],
            'registration_house'       => $params['registration_house'],
            'registration_apartment'   => $params['registration_apartment'],
            'amount'                   => $params['amount'],
            'term'                     => $params['term'],
            'scorista'                 => $params['scorista'],
            'utm_source'               => $params['utm_source'],
            'utm_medium'               => $params['utm_medium'],
            'utm_campaign'             => $params['utm_campaign'],
            'wm_id'                    => $params['wm_id'],
            'click_id'                 => $params['click_id'],
            'juicy_id'                 => $params['juicy_id'],
        ];

        list($response, $code) = $this->json($path, $payload, $token);

        return $code == 200 ? $response : null;
    }

    /**
     * @param array $params
     * @param string $site_id
     * @return array|null
     * @throws Exception
     */
    public function sendOverduedPayments($params, $site_id)
    {
        $this->db->query("SELECT token FROM application_tokens WHERE `enabled` > 0 AND `app` = 'bonon_overdued_payments' AND site_id = ?", $site_id);
        $token = $this->db->result('token');
        $path  = $this->settings->bonon_overdued_payments_path;
        if (empty($token)) {
            throw new LogicException('Bonondo api token not specified');
        }

        $payload = [
            'phone'                    => $params['phone'],
            'email'                    => $params['email'],
            'first_name'               => $params['first_name'],
            'patronymic'               => $params['patronymic'],
            'last_name'                => $params['last_name'],
            'birthdate'                => $params['birthdate'],
            'passport_series'          => $params['passport_series'],
            'passport_number'          => $params['passport_number'],
            'passport_date'            => $params['passport_date'],
            'passport_department_code' => $params['passport_department_code'],
            'birth_place'              => $params['birth_place'],
            'gender'                   => $params['gender'],
            'registration_region'      => $params['registration_region'],
            'registration_city'        => $params['registration_city'],
            'registration_street'      => $params['registration_street'],
            'registration_house'       => $params['registration_house'],
            'registration_apartment'   => $params['registration_apartment'],
            'amount'                   => $params['amount'],
            'term'                     => $params['term'],
            'scorista'                 => $params['scorista'],
            'utm_source'               => $params['utm_source'],
            'utm_medium'               => $params['utm_medium'],
            'utm_campaign'             => $params['utm_campaign'],
            'wm_id'                    => $params['wm_id'],
            'click_id'                 => $params['click_id'],
            'juicy_id'                 => $params['juicy_id'],
        ];

        list($response, $code) = $this->json($path, $payload, $token);

        return $code == 200 ? $response : null;
    }

    /**
     * @param string $uri
     * @param array $payload
     * @return array
     * @throws Exception
     */
    private function json($uri, $payload, $token)
    {
        $url = $this->createUrl($uri);

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "apptoken: $token",
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
                "CODE: $code" . PHP_EOL . $response,
                'bonondo_api.txt'
            );
        }

        return [$data, $code];
    }

    /**
     * @param  string $uri
     * @return string
     */
    private function createUrl($uri)
    {
        return "{$this->baseUrl}{$uri}";
    }
}