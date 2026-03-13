<?php

require_once 'Simpla.php';


class Finroznica extends Simpla
{
    private $token;
    private $url = 'http://manager.quick-finance.ru/';

    public function __construct()
    {
        parent::__construct();

        $this->token = $this->config->fr_token;
        $this->enabled = $this->config->fr_enabled || $this->is_developer;
    }

    public function send_user($user)
    {
        if (empty($this->enabled))
            return false;

        $this->load(
            'partner_callback',
            false,
            [
                'token' => $this->token,
                'phone' => $user->phone_mobile,
                'firstname' => $user->firstname,
                'partner' => 'boostra'
            ]
        );
    }


    /**
     * Finroznica::load()
     *
     * @param string $method
     * @param array $data
     * @param bool $post
     * @return
     */
    public function load($method, $post = false, $data = array())
    {
        $url = $this->url.$method;

        $headers = array(
            'Accept: application/json',
            'Authorization: Bearer '.$this->token
        );

        if (!$post)
        {
            $params = http_build_query($data);
            $url .= '?' . $params;
        }
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($post)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $res = curl_exec($ch);
        curl_close($ch);

        $this->logging(__METHOD__, $method, $data, $res, 'finroznica.txt');

        return $res;
    }
}