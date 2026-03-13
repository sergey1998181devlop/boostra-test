<?php

require_once 'AService.php';

class BlacklistSync extends AService
{
    public function __construct()
    {
        parent::__construct();
        $this->handle_api_method();
        $this->json_output();
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

    /**
     * Вызывается когда клиент был добавлен в ЧС 1С.
     * @param string $uid
     * @param string $reason
     * @return string|string[]
     */
    public function add(string $uid, string $reason = 'Получен из 1С')
    {
        $users = $this->users->get_users([
            'uid' => $uid
        ]);
        if (empty($users))
            return ['error' => 'Unknown user.'];

        foreach ($users as $user) {
            $id = $this->blacklist->add([
                'user_id' => $user->id,
                'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                'comment' => '',
                'reason' => $reason,
            ]);
            if (empty($id))
                continue;

            $orders = $this->orders->get_orders([
                'user_id' => $user->id,
                'status' => [
                    $this->orders::ORDER_STATUS_CRM_NEW,
                    $this->orders::ORDER_STATUS_CRM_APPROVED
                ]
            ]);
            if (empty($orders))
                continue;

            foreach ($orders as $order) {
                $this->orders->rejectOrder($order, $this->reasons::REASON_BLACK_LIST);
            }
        }

        return 'Success';
    }

    /**
     * Вызывается когда клиент был удалён из ЧС 1С.
     * @param string $uid
     * @return string|string[]
     */
    public function remove(string $uid)
    {
        $users = $this->users->get_users([
            'uid' => $uid
        ]);
        if (empty($users))
            return ['error' => 'Unknown user.'];

        foreach ($users as $user)
            $this->blacklist->delete($user->id);
        return 'Success';
    }
}

new BlacklistSync();
