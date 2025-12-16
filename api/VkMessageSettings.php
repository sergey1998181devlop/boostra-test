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
        $this->vkBotApiToken = 'dzezRQFa8rEfxjsAYtCWk2E0sj';
        // TODO
        //$this->vkBotApiToken = $this->settings->apikeys['vk_bot_api']['token'];
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
     * Пользователь перешёл по ссылке отправленной ВК ботом.
     *
     * Необходимо для корректного сбора статистики.
     * @param integer $userId
     * @param string $code
     * @return void
     */
    public function sendLinkVisited($userId, $code)
    {
        try {
            $this->requestBotApi('link_visited', true, [
                'user_id' => $userId,
                'code' => $code
            ]);
        }
        catch (Exception $e) {
            // На случай если API ляжет/будет перезагружаться
        }
    }
}