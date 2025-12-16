<?php

require_once 'Simpla.php';

class VkApi extends Simpla
{
    const VERSION = '5.199';
    const URL = 'https://api.vk.com/method/';
    const TOKEN = 'f3e3badff3e3badff3e3badf93f0fb87caff3e3f3e3badf95d8f74e8253198d2666d2ca';

    /** @var string Пользователь с таким номером не найден */
    const AUTH_RESULT_NOT_FOUND = 'NOT_FOUND';
    /** @var string Не удалось получить номер телефона из ответа */
    const AUTH_RESULT_CANT_GET_PHONE = 'CANT_GET_PHONE';
    /** @var string Ошибка при запросе к VK API */
    const AUTH_RESULT_BAD_REQUEST = 'BAD_REQUEST';

    public function request(string $method, array $params = [])
    {
        $params['v'] = self::VERSION;
        $params['access_token'] = self::TOKEN;
        $url = self::URL . $method;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        try {
            $responseDecoded = json_decode($response, true);
            if (!isset($responseDecoded['response']))
                throw new Exception();
            return $responseDecoded['response'];
        } catch (Exception $error) {
            $details = $url . "\nHTTP Code: " . $httpCode . "\nError: " . $error->getMessage();
            $this->logging(__METHOD__, $details, $params, $response, 'vk_auth.txt');
            return null;
        }
    }

    /**
     * Обработка авторизации через VK ID. Возвращает пользователя, либо ошибку в виде строки
     * @param object $payload
     * @return string|object
     */
    public function authHandler(object $payload)
    {
        $response = $this->request('auth.exchangeSilentAuthToken', [
            'token' => $payload->token,
            'uuid' => $payload->uuid
        ]);

        if (empty($response))
            return self::AUTH_RESULT_BAD_REQUEST; // Может вызваться при разных лагах, т.к. токен клиента живёт 5 минут

        if (empty($response['phone']))
            return self::AUTH_RESULT_CANT_GET_PHONE;

        $user = $this->users->get_user($response['phone']);
        if (empty($user))
            return self::AUTH_RESULT_NOT_FOUND;

        if (!empty($this->get($user->id)))
        {
            // Повторная авторзиация через VK ID
            $this->update($user->id, [
                'access_token_id' => $response['access_token_id'],
                'access_token' => $response['access_token'],
            ]);
        }
        else
        {
            // Первая авторизация через VK ID
            $this->add([
                'user_id' => $user->id,
                'vk_user_id' => $response['user_id'],
                'access_token_id' => $response['access_token_id'],
                'access_token' => $response['access_token'],
                'phone' => $response['phone'],
                'email' => $response['email'],
            ]);
        }

        return $user;
    }

    // region Работа с БД

    public function add(array $row)
    {
        $this->db->query($this->db->placehold('INSERT INTO __user_vk SET ?%', $row));
    }

    public function get(int $user_id)
    {
        return $this->getBy('user_id', $user_id);
    }

    public function getByVkUserId(int $vk_user_id)
    {
        return $this->getBy('vk_user_id', $vk_user_id);
    }

    public function getBy(string $column, $value)
    {
        $this->db->query($this->db->placehold("SELECT * FROM __user_vk WHERE `$column` = ?", $value));
        return $this->db->result();
    }

    public function update($user_id, $data)
    {
        $data['updated'] = date('Y-m-d H:i:s');
        $this->db->query($this->db->placehold("UPDATE __user_vk SET ?% WHERE id = ?", $data, $user_id));
    }

    // endregion
}