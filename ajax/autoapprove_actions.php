<?php
error_reporting(0);
ini_set('display_errors', 'Off');
date_default_timezone_set('Europe/Moscow');

header('Content-type: application/json; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');
define('ROOT', dirname(__DIR__));

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
 * Теперь в нашем АПИ есть действие test, он ожидает параметры a, b и имеет необязательный параметр c
 * В ответ на обращение к апи мы получим результат a + b * c
 */

class AutoapproveActions extends Simpla
{
    private $response = [];
    private $user;

    public function __construct()
    {
        parent::__construct();
        if ($this->can_access_api())
            $this->handle_api_action();
        else
            $this->response['error'] = 'Unknown user.';

        echo json_encode($this->response);
    }

    /**
     * true, если разрешён доступ к апи.
     * @return bool
     */
    private function can_access_api()
    {
        $this->user = $this->users->get_user((int)$_SESSION['user_id']);
        return !empty($this->user);
    }

    /**
     * Получает имя метода из GET параметра "action".
     * Если метод существует в классе и он public - вызывает его.
     */
    private function handle_api_action()
    {
        $action = $this->request->get('action');
        if (empty($action)) {
            $this->response['error'] = '"action" param is empty.';
            return;
        }

        if (method_exists($this, $action)) {
            $reflection = new ReflectionMethod($this, $action);
            if ($reflection->isPublic()) {
                $required_params = $reflection->getParameters();
                $params = [];
                foreach ($required_params as $param) {
                    $param_name = $param->getName();
                    $param_value = $this->request->get($param_name);
                    if (!isset($param_value)) {
                        if ($param->isOptional()) {
                            $params[] = $param->getDefaultValue();
                            continue;
                        } else {
                            $this->response['error'] = 'Action "' . $param_name . '" argument is missing.';
                            return;
                        }
                    }
                    $params[] = $param_value;
                }
                $return = $this->$action(...$params);
                if (!empty($return))
                    $this->response = $return;
                return;
            }
        }
        $this->response['error'] = 'Wrong action.';
    }

    /**
     * Получение последней автоодобренной заявки.
     * @return ArrayObject|null
     */
    private function get_autoapprove_order()
    {
        $order = $this->orders->get_last_order($this->user->id);
        if (empty($order))
            return null;

        if ($order->utm_source !== 'crm_auto_approve')
            return null;

        return $order;
    }

    /**
     * Отклонение автоодобренной заявки без СМС отказникам.
     */
    public function reject()
    {
        if ($last_order = $this->get_autoapprove_order()) {
            if ($last_order->status != $this->orders::STATUS_APPROVED)
                return "Can't reject this order";

            $this->orders->update_order($last_order->id, [
                'status' => $this->orders::STATUS_REJECTED,
                'reject_date' => date('Y-m-d H:i:s')
            ]);
            $this->soap->set_tehokaz($last_order->{'1c_id'});
            $this->response['success'] = 'OK';
        }
    }
}

(new AutoapproveActions());