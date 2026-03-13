<?php

require_once 'Simpla.php';

/**
 * Class VkMessageSettings
 * s_vk_message_settings
 */
class VkMessageSettings extends Simpla
{
    private const VK_BOT_API_URL = 'http://158.160.92.119:3000/api/';
    private $vkBotApiToken;

    public function __construct()
    {
        parent::__construct();
        $this->vkBotApiToken = $this->settings->apikeys['vk_bot_api']['token'];
    }


    /**
     * Методы для работы с таблицей настроек
     */

    /**
     * Получение всех записей
     * @return array
     */
    public function getAll()
    {
        $this->db->query("SELECT * FROM __vk_message_settings");
        return $this->db->results() ?: [];
    }

    /**
     * Получение всех соответствующих записей
     *
     * ```
     * $rows = $this->vk_message_settings->getWhere([
     *  'utm_source' => 'crm_auto_approve',
     *  'send_hour' => [14, 15],
     * ]);
     * ```
     *
     * @param $columns
     * @return array
     */
    public function getWhere($columns)
    {
        if (empty($columns))
            return $this->getAll();

        $where = [];
        foreach ($columns as $column => $value) {
            if (is_array($value))
                $where[] = $this->db->placehold("$column IN (?@)", $value);
            else
                $where[] = $this->db->placehold("$column = ?", $value);
        }
        $where = implode(' AND ', $where);

        $this->db->query("SELECT * FROM __vk_message_settings WHERE $where");
        return $this->db->results() ?: [];
    }

    /**
     * Получение конкретной записи по её Id
     * @param $id
     * @return false|ArrayObject
     */
    public function get($id)
    {
        $query = $this->db->placehold('SELECT * FROM __vk_message_settings WHERE id = ?', $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add($row)
    {
        $query = $this->db->placehold('INSERT INTO __vk_message_settings SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        $query = $this->db->placehold("UPDATE __vk_message_settings SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Удаление записи
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $query = $this->db->placehold("DELETE FROM __vk_message_settings WHERE id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Включена ли рассылка сообщений в ВК
     * @return bool
     */
    public function isEnabled()
    {
        $isEnabled = $this->settings->vk_bot_enabled;
        return !empty($isEnabled);
    }


    /**
     * Методы для работы с API бота
     */

    /**
     * Запрос к АПИ бота
     * @param string $method
     * @param bool $isPost
     * @param array $jsonData
     * @return array
     * @throws Exception
     */
    private function requestBotApi($method, $isPost = false, $jsonData = [])
    {
        $url = self::VK_BOT_API_URL . $method;
        $headers = [
            "Authorization: Bearer $this->vkBotApiToken"
        ];

        if (!empty($jsonData)) {
            $headers[] = 'Content-Type: application/json';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        if (!empty($jsonData)) {
            $jsonData = json_encode($jsonData);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            throw new Exception("Ошибка при запросе, код ответа: $httpCode\n$response");
        }

        curl_close($ch);

        $data = json_decode($response, true);
        return $data;
    }

    /**
     * Получение списка из id пользователей которым можно сделать рассылку
     * @return array
     * @throws Exception
     */
    public function getVkUsers()
    {
        return $this->requestBotApi('get_users') ?: [];
    }

    /**
     * Рассылка сообщений в ВК
     *
     * ```
     * // Ожидаемый формат аргумента
     * $messages = [
     *      [
     *          'user_id' => 123, // id клиента с бустры
     *          'text' => 'Текст персонализированного сообщения',
     *          'setting_id' => 1 // Необязательный параметр, нужен для статистики
     *      ],
     *      [
     *          'user_id' => 292,
     *          'text' => '...'
     *      ]
     * ];
     * ```
     * @param array $messages
     * @return void
     * @throws Exception
     */
    public function sendVkMessages($messages)
    {
        $this->requestBotApi('send_users', true, [
            'messages' => $messages
        ]);
    }

    /**
     * Получение статистики
     * @return array
     * @throws Exception
     */
    public function getVkStatistic()
    {
        return $this->requestBotApi('get_statistic') ?: [];
    }
}