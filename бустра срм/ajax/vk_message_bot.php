<?php

date_default_timezone_set('Europe/Moscow');

header('Content-type: application/json; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');

session_start();
chdir('..');

require 'api/Simpla.php';

/*
 * Любые публичные функции этого класса можно вызвать при обращении к апи
 * Аргументы функций будут автоматически браться из get запроса
 * Аргументы могут иметь значение по-умолчанию
 * Задать ответ можно используя return или напрямую отредактировав массив $this->response
 * Ответ будет преобразован в JSON
 *
 * ПРИМЕР
 * public function test(a, b, c = 100)
 * {
 *    return a + b * c;
 * }
 * Теперь в нашем АПИ есть метод test, он ожидает параметры a, b и имеет необязательный параметр c
 * В ответ на обращение к апи мы получим результат a + b * c
 */

class VkMessageBot extends Simpla
{
    const TOKEN = 'HSeiQY8jWkvzqrrvwpV0yzOszx';

    private $response = [];

    public function __construct()
    {
        parent::__construct();
        if ($this->can_access_api())
            $this->handle_api_method();
        else
            $this->response['error'] = 'Wrong token.';

        echo json_encode($this->response);
    }

    /**
     * true, если разрешён доступ к апи.
     * @return bool
     */
    private function can_access_api()
    {
        $token = $this->request->post('token');
        return !empty($token) && $token == self::TOKEN;
    }

    /**
     * Получает имя метода из GET параметра "method".
     * Если метод существует в классе и он public - вызывает его.
     */
    private function handle_api_method()
    {
        $method = $this->request->post('method');
        if (empty($method))
        {
            $this->response['error'] = '"method" param is empty.';
            return;
        }

        if (method_exists($this, $method))
        {
            $reflection = new ReflectionMethod($this, $method);
            if ($reflection->isPublic())
            {
                $required_params = $reflection->getParameters();
                $params = [];
                foreach ($required_params as $param)
                {
                    $param_name = $param->getName();
                    $param_value = $this->request->post($param_name);
                    if (!isset($param_value))
                    {
                        if ($param->isOptional())
                        {
                            $params[] = $param->getDefaultValue();
                            continue;
                        }
                        else
                        {
                            $this->response['error'] = 'Method "' . $param_name . '" argument is missing.';
                            return;
                        }
                    }
                    $params[] = $param_value;
                }
                $return = $this->$method(...$params);
                if (!empty($return))
                    $this->response = $return;
                return;
            }
        }
        $this->response['error'] = 'Wrong method.';
    }

    public function get_boostra_id($vk_user_id)
    {
        $user_vk = $this->vk_api->getByVkUserId($vk_user_id);
        if (empty($user_vk))
            return 'User not found.';
        else
            return $user_vk->user_id;
    }

    public function is_enabled()
    {
        return $this->vk_message_settings->isEnabled();
    }
}

(new VkMessageBot());
