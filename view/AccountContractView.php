<?php

require_once('View.php');
require_once(dirname(__DIR__) . '/ajax/LoginClass.php');

/**
 * Class AccountContractView
 * Класс осуществляющий вход в ЛК по договору (ФИО)
 */
class AccountContractView extends View
{
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');
        if (method_exists(self::class, $action)) {
            $this->{$action}();
        } elseif (!empty($action)) {
            throw new Exception('This method denied');
        }
    }

    /**
     * @return false|string|void
     */
    function fetch()
    {
        if (!$this->contract_user) {
            return $this->design->fetch('contract/contract_login.tpl');
        }

        ! session_id() && @session_start();
        
        $this->design->assign('contract_user',  $this->contract_user);
        $this->design->assign('session_key',  $this->account_contract::SESSION_KEY);
        
        $response1C = $this->account_contract->getUserContracts($this->contract_user->uid);
        if(empty($response1C['errors'])) {
            foreach ($response1C as $key => $loan) {
                if ($loan['IL'] != 0) {
                    $response1C[$key]['IL_DATA'] = $this->account_contract->getIlDetail($loan['НомерЗайма']);
                }
            }

            $_SESSION[$this->account_contract::SESSION_KEY] = $response1C;
        }

        return $this->design->fetch('contract/contract_account.tpl');
    }

    /**
     * Авторизация пользователя
     * @return void
     * @throws Exception
     */
    private function login()
    {
        $response = [];
        $data = [
            'Passport' => $this->request->post('passport'),
            'DR' => $this->request->post('birthday'),
        ];
        $errors = $this->validate($data);
        if (empty($errors)) {
            if ($user = $this->users->getUserByContract($data)) {
                $response1C = $this->account_contract->getUserContracts($user->uid);
                if (empty($response1C['errors'])) {

                    foreach ($response1C as $key => $loan) {
                        if ($loan['IL'] != 0) {
                            $response1C[$key]['IL_DATA'] = $this->account_contract->getIlDetail($loan['НомерЗайма']);
                        }
                    }

                    $_SESSION['contract_user_id'] = $user->id;
                    $_SESSION[$this->account_contract::SESSION_KEY] = $response1C;
                    $response['result'] = $this->config->root_url . '/user/contract';
                } else {
                    $response['errors'][] = 'Ошибка сервера.';
                }
            } else {
                $response['errors'][] = 'Проверьте данные, пользователь не найден';
            }
        } else {
            $response['errors'] = $errors;
        }
        $this->request->json_output($response);
    }

    /**
     * Валидация на пустые поля
     * @param $data
     * @return string[]
     */
    private function validate($data)
    {
        $errors = array_map(function ($item){
            return 'Заполните поле ' . $item;
        },array_keys(array_filter($data, function ($item){
            return empty($item);
        })));

        return $errors;
    }

    /**
     * Получает ссылку на оплату
     * @return void
     */
    private function getPaymentLink()
    {
        $amount = (float)$this->request->post('amount');
        $contract_number = (string)$this->request->post('contract_number');
        if (empty($amount)) {
            $response['errors'][] = 'Сумма должна быть больше 0';
        } else {
            $response = $this->account_contract->getPaymentLink($this->contract_user, $amount, $contract_number);
        }

        $this->request->json_output($response);
    }
}