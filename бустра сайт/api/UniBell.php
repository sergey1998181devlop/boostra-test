<?php

require_once(__DIR__ . '/../api/Simpla.php');


/**
 * Класс для работы с UniBell
 * Class UniBell
 */
class UniBell extends Simpla
{
    /**
     * Ключ для работы с апи
     */
    const API_KEY = 'PnYLAworTdFulbmQSTWex3HP050zupnv';

    /**
     * Основной URL
     */
    const URL = 'https://api.unibell.ru/';

    /**
     * Формируем запрос для отправки
     * @param $method
     * @param $data
     * @param bool $post
     * @return array
     */
    private function sendRequest($method, $data, bool $post = true) : array
    {
        $url = self::URL . $method;

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic ' . self::API_KEY
        );

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $res = curl_exec($ch);
        curl_close($ch);

        $this->logging(__METHOD__, $url, $data, $res, 'uniball.txt');

        return json_decode($res, true);
    }

    /**
     * Отправка кода
     * @param string $phone
     * @return array
     */
    public function sendFlash(string $phone)
    {
        $data = [
            'number' => $phone, // номер телефона в формате e.164
            'code' => rand(1000, 9999), // код, 4е знака
            'timeout' => 10000 // максимальное время вызова в мс
        ];

        $response = $this->sendRequest('apps/flash/calls/flash', $data);

        $insert_data = [
            'phone' => $phone,
            'code' => $data['code'],
            'status' => 'NEW',
        ];

        if (!empty($response['requestId'])) {
            $insert_data['request_id'] = $response['requestId'];
        }

        if (!empty($response['errorCode'])) {
            $insert_data['error_code'] = $response['errorCode'];
        }

        $this->addSms($insert_data);

        return $response;
    }

    public function callBackStatus()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data['requestId'])) {
            $this->updateSms($data['requestId'], ['status' => $data['status']]);
        }
    }

    /**
     * Добавляет запись в БД
     * @param $data
     * @return void
     */
    public function addSms($data)
    {
        $query = $this->db->placehold("INSERT INTO s_unibell_sms SET ?%", $data);
        $this->db->query($query);
    }

    /**
     * Обновляет запись в БД
     * @param $request_id
     * @param $data
     * @return void
     */
    public function updateSms($request_id, $data)
    {
        $query = $this->db->placehold("UPDATE s_unibell_sms SET ?% WHERE request_id = ?", $data, $request_id);
        $this->db->query($query);
    }
}
