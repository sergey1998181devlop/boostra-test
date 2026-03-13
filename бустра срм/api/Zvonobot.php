<?php

/**
 * Simpla CMS
 *
 * @copyright    2011 Denis Pikusov
 * @link        http://simplacms.ru
 * @author        Denis Pikusov
 *
 */

require_once('Simpla.php');

class Zvonobot extends Simpla
{
    public function call($phone)
    {
        if ($curl = curl_init()) {
            $json = '{
                  "apiKey": "' . $this->config->api_zvonobot . '",
                  "phone": "' . $phone . '",
                  "outgoingPhone": "' . $this->config->outgoing_phone . '",
                  "record": {
                    "id": ' . $this->config->record_id . '
                  }
                }';

            curl_setopt($curl, CURLOPT_URL, 'https://lk.zvonobot.ru/apiCalls/create');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
            $out = curl_exec($curl);

            curl_close($curl);

            return json_decode($out, true);
        }
    }

    public function check($id)
    {
        $requestArray = array(
            'apiKey' => $this->config->api_zvonobot
        );

        $json = json_encode($requestArray);

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, 'https://lk.zvonobot.ru/apiCalls/get?apiCallIdList[]=' . $id);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $out = curl_exec($curl);

            curl_close($curl);

            return json_decode($out, true);
        }
    }
}