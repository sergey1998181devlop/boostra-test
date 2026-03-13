<?php

require_once('../api/Simpla.php');

class Bias extends Simpla
{
    private $login = '';
    private $password = '';

    public function __construct()
    {
        parent::__construct();

        $this->login = 'yno_khmelik';//$this->settings->apikeys['bias']['login'];
        $this->password = 'aN5YN(vH';//$this->settings->apikeys['bias']['password'];
    }

    public function run_scoring($scoring_id)
    {
        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            if ($order = $this->orders->get_order((int)$scoring->order_id)) {
                if (empty($order->passport_serial)) {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не достаточно данных для проведения скоринга'
                    );
                } else {
                    $passport_serial = str_replace(array(' ', '-'), '', $order->passport_serial);
                    $response = $this->searching_data($passport_serial);

                    $result = json_decode($response, true);

                    if ($response) {
                        if (!empty($result['errors'])) {
                            $update = array(
                                'status' => $this->scorings::STATUS_ERROR,
                                'success' => 0,
                                'string_result' => $result['errors']
                            );
                        } else {
                            $statuses = array(
                                0 => 'в процессе',
                                1 => 'запрос выполнен,ответ получен',
                                2 => 'запрос выполнен,получен пустой ответ',
                                3 => 'ошибка выполнения запроса',
                                4 => 'ошибка отправки запроса или недостаточность условий для выполнения запрос'
                            );

                            $statusesId = $result['profiles'][0]["serviceRequests"][0]['state'];

                            if ($statusesId) {
                                $update = array(
                                    'status' => $this->scorings::STATUS_COMPLETED,
                                    'body' => serialize($response),
                                    'success' => 1,
                                    'string_result' => $statuses[$statusesId]
                                );
                            } else {
                                $update = array(
                                    'status' => $this->scorings::STATUS_COMPLETED,
                                    'body' => serialize($response),
                                    'success' => 1,
                                    'string_result' => 'неизвестный результат'
                                );
                            }
                        }
                    } else {
                        $update = array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'string_result' => 'не удалось выполнить запрос'
                        );
                    }
                }
            } else {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                );
            }

            if (!empty($update))
            {
                $this->scorings->update_scoring($scoring_id, $update);
            }

            return $update;
        }
    }

    public function searching_data($sernum)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://igls2.bias.ru/api/request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
              "login": "' . $this->login . '",
              "password": "' . $this->password . '",
              "timeout": -1,
              "services": [
                "600000"
              ],
              "searchFields": [
                {
                  "name": "sernum",
                  "value": "' . $sernum . '"
                }
              ]
            }',
            CURLOPT_SSL_VERIFYHOST => 0,  // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}