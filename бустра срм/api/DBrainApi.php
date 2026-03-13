<?php

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */

require_once('Simpla.php');

class DBrainApi extends Simpla
{
    private const DBRAIN_API = 'https://latest.dbrain.io/';

    const STATUS_SENDED = 1;
    const STATUS_RECEIVED = 2;

    //  Типы фотографий
    const DOC_PASSPORT = 'passport_main';
    const DOC_CARD = 'bank_card';

    //  Типы операций над фотографиями
    private const METHOD_PULL = 'pull';
    private const METHOD_VERIFY = 'verify';

    private const METHOD_CHECK_ANTIFRAUD = 'fraud';

    private $token;

    public function __construct()
    {
        parent::__construct();

        $this->token = $this->settings->apikeys['dbrain']['api_key'];
    }

    /**
     * Получение ссылки на файл по его id.
     * @param int $file_id
     * @return string
     */
    private function get_file_url($file_id)
    {
        $query = $this->db->placehold('SELECT `name`, user_id FROM __files WHERE id = ? LIMIT 1', $file_id);
        $this->db->query($query);
        $file = $this->db->result();

        $file_path = $this->config->front_url . '/' . $this->config->original_images_dir . $file->name;
        if (!$this->isFileExists($file_path))
            // Подгружаем фото с хранилища если их нет на сервере
            $this->loadFileStorage($file->user_id);

        return $file_path;
    }

    private function loadFileStorage($user_id)
    {
        $url = $this->config->front_url . "/ajax/filestorage.php?user_id=" . urlencode($user_id);
        @file_get_contents($url);
    }

    function isFileExists($url) {
        $headers = @get_headers($url);
        if ($headers && strpos($headers[0], '200') !== false) {
            return true; // Всё ок, сервер ответил 200
        }
        return false;
    }

    /**
     * Отправка асинхронного запроса.
     * @param string $method
     * @param array $params
     * @param array $files
     * @return array
     */
    private function send_request($method, $params = [], $files = [])
    {
        $headers = ['accept' => 'application/json'];

        foreach ($files as &$file)
        {
            $file = new CURLFile($file);
        }

        $ch = curl_init(self::DBRAIN_API . $method . '?async=true&token=' . $this->token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($params))
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        if (!empty($files))
            curl_setopt($ch, CURLOPT_POSTFIELDS, $files);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * Получение результата отправленного запроса.
     * @param string $task_id
     * @return array
     */
    private function get_response($task_id)
    {
        $headers = ['accept' => 'application/json'];

        $ch = curl_init(self::DBRAIN_API . 'result/' . $task_id . '?token=' . $this->token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);$result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * Получение сохранённого результата последнего запроса.
     * @param string|int $id int - file_id, string - task_id
     * @param string $method
     * @return array|null
     */
    private function get_existing_response($id, $method = '')
    {
        if (is_int($id)) {
            //  Поиск по file_id и method
            $query = $this->db->placehold(
                'SELECT `task_id`, `result`, `method` FROM __dbrain WHERE `method` = ? AND `status` = ? AND `file_id` = ? ORDER BY id DESC LIMIT 1',
                $method, self::STATUS_RECEIVED, $id);
        }
        else {
            //  Поиск по task_id
            $query = $this->db->placehold(
                'SELECT `task_id`, `result`, `method` FROM __dbrain WHERE `status` = ? AND `task_id` = ? ORDER BY id DESC LIMIT 1',
                self::STATUS_RECEIVED, $id);
        }
        $this->db->query($query);
        $row = $this->db->result();
        if (!empty($row))
        {
            $row->result = json_decode($row->result, true);
            return $row;
        }
        return null;
    }

    /**
     * Вытягивание полей из документа, асинхронный запрос.
     *
     * Результат запроса возвращает метод get_result($task_id)
     * @param int $file_id
     * @param string $doc_type
     * @return string|bool $task_id либо false, если что-то пошло не так.
     * @see get_result
     */
    public function pull_fields($file_id, $doc_type)
    {
        if ($existing = $this->get_existing_response($file_id, self::METHOD_PULL))
            return $existing->result['task_id'];

        $files = array('image' => $this->get_file_url($file_id));
        $params = array('doc_type' => $doc_type);
        $response = $this->send_request('recognize', $params, $files);
        if (empty($response) || empty($response['task_id']))
            return false;

        $this->db->query('INSERT INTO __dbrain SET ?%', [
            'file_id' => $file_id,
            'method' => self::METHOD_PULL,
            'task_id' => $response['task_id'],
            'status' => self::STATUS_SENDED
        ]);

        return $response['task_id'];
    }

    /**
     * Проверка документа на подлинность, асинхронный запрос.
     *
     * Результат запроса возвращает метод get_result($task_id)
     * @param int $file_id
     * @param string $doc_type
     * @return string|bool $task_id либо false, если что-то пошло не так.
     * @see get_result
     */
    public function verify_file($file_id, $doc_type)
    {
        if ($existing = $this->get_existing_response($file_id, self::METHOD_VERIFY))
            return $existing->result['task_id'];

        $files = array('image' => $this->get_file_url($file_id));
        $params = array('doc_type' => $doc_type);
        $response = $this->send_request('check/fraud', $params, $files);
        if (empty($response) || empty($response['task_id']))
            return false;

        $this->db->query('INSERT INTO __dbrain SET ?%', [
            'file_id' => $file_id,
            'method' => self::METHOD_VERIFY,
            'task_id' => $response['task_id'],
            'status' => self::STATUS_SENDED
        ]);

        return $response['task_id'];
    }

    /**
     * Возвращает результат асинхронного запроса.
     *
     * Вместо массива может вернуть один из следующих кодов:
     *```
     * 202 - Результат ещё не готов, нужно вызвать метод позже.
     * 404 - Результат с таким task_id не найден.
     * ```
     * @param string $task_id
     * @return array|int Результат в виде массива ИЛИ код ответа от DBrain, если запрос ещё не готов.
     */
    public function get_result($task_id)
    {
        if ($existing = $this->get_existing_response($task_id))
            return $existing->result;

        $result = $this->get_response($task_id);
        if ($result['status_code'] != 200)
            return $result['status_code'];

        $query = $this->db->placehold('UPDATE __dbrain SET ?% WHERE task_id = ?', [
            'status' => self::STATUS_RECEIVED,
            'result' => json_encode($result)
        ], $task_id);
        $this->db->query($query);

        return $result;
    }

    /**
     * Возвращает наиболее подходящий элемент из массива ``$result['items']``.
     * @param $result
     * @param null $doc_type
     * @return array|null
     */
    private function get_best_items_result($result, $doc_type = null)
    {
        try {
            $best_result = null;
            $best_confidence = 0;
            foreach ($result['items'] as $item) {
                if (!empty($doc_type) && $item['doc_type'] != $doc_type)
                    continue;
                if ($item['confidence'] > $best_confidence) {
                    $best_confidence = $item['confidence'];
                    $best_result = $item;
                }
            }
            return $best_result;
        }
        catch (Exception $e) {
            return null;
        }
    }

    /**
     * Получение конкретного значения из ответа get_result.
     * ```php
     * $result = $simpla->dbrain->get_result($task_id);
     * $field = $simpla->dbrain->get_field($result, "first_name");
     * $name = $field["text"]; // Текст поля.
     * $confidence = $field["confidence"]; // Уверенность в полученных данных (От 0 до 1).
     * ```
     * @param array $result
     * @param string $field_name
     * @return array|null
     * @see get_result
     */
    public function get_field($result, $field_name)
    {
        try {
            $field = $this->get_best_items_result($result)['fields'][$field_name];
            return [
                'text' => $field['text'],
                'confidence' => $field['confidence']
            ];
        }
        catch (Exception $e) {
            return null;
        }
    }

    /**
     * Получение всех значений из ответа get_result.
     * @param $result
     * @return array|null
     * @see get_fields
     * @see get_result
     */
    public function get_fields($result)
    {
        try {
            $fields = $this->get_best_items_result($result)['fields'];
            $result = [];
            foreach ($fields as $field_name => $field) {
                $result[$field_name] = [
                    'text' => $field['text'],
                    'confidence' => $field['confidence']
                ];
            }
            return $result;
        }
        catch (Exception $e) {
            return null;
        }
    }

    public function get_custom_fields($result, $params, $doc_type)
    {        
        try {
            $fields = $this->get_best_items_result($result, $doc_type)['fields'];
            $result = [];
            foreach ($fields as $field_name => $field) {
                if (isset($params[$field_name])) {
                    $confidence = round($field['confidence'] * 100);
                    $result[$params[$field_name][0]] = [
                        'text' => $field['text'],
                        'confidence' => $confidence,
                        'success' => intval($confidence >= $params[$field_name][1]),
                    ];                    
                }
            }
            return $result;
        }
        catch (Exception $e) {
            return null;
        }
    }

    public function is_dbrain_card_required($order)
    {
        if (is_int($order) || is_string($order))
            $order = $this->orders->get_order($order);
        if (is_array($order))
            $order = (object)$order;

        if ($order->have_close_credits == 0)
            return True;

        // Старые заявки
        $orders = $this->orders->get_orders([
            'user_id' => $order->user_id,
        ]);

        $cards = [];
        foreach ($orders as $old_order) {
            if ($old_order->order_id == $order->order_id)
                continue;
            $cards[] = $old_order->card_id;
        }

        if (in_array($order->card_id, $cards))
            return False;
        return True;
    }

    public function checkAntiFraud($file_id)
    {
        if ($existing = $this->get_existing_response($file_id, self::METHOD_CHECK_ANTIFRAUD))
            return $existing->result['task_id'];

        $files = ['image' => $this->get_file_url($file_id)];
        $params = ['return_crops' => false];

        $response = $this->send_request('pipelines/run/fraud_v2', $params, $files);
        if (empty($response) || empty($response['task_id']))
            return false;

        $this->db->query('INSERT INTO __dbrain SET ?%', [
            'file_id' => $file_id,
            'method' => self::METHOD_CHECK_ANTIFRAUD,
            'task_id' => $response['task_id'],
            'status' => self::STATUS_SENDED
        ]);

        return $response['task_id'];
    }
}
