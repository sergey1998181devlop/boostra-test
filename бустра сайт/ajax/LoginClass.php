<?php

namespace ajax\loginClass;

require_once(dirname(__DIR__) . '/api/Simpla.php');
require_once(dirname(__DIR__) . '/api/VerifyConnection/VerifyConnectionHandler.php');

/**
 * Class LoginClass
 */
class LoginClass
{
    private $simpla;

    public function __construct()
    {
        if (!session_id()) {
            session_start();
        }
        $this->simpla = new \Simpla();
    }

    /**
     * Возвращаем данные
     * @param $data
     * @return false|string
     */
    private function response($data = [])
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");

        echo json_encode($data);
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
     * Метод проверки пользователя по паспортным данным
     * @return void
     */
    public function loginUserByPassport()
    {
        $response = [];

        $data['Passport'] = $this->simpla->request->post('passport');
        $data['DR'] = $this->simpla->request->post('birthday');

        $validate_errors = $this->validate($data);

        if(isset($_SESSION['check_login_count'])) {
            $_SESSION['check_login_count']++;
        } else {
            $_SESSION['check_login_count'] = 1;
        }

        if ($_SESSION['check_login_count'] > 5 && empty($this->is_developer) && empty($this->is_admin))
        {
            $validate_errors['login_count'] = 'Превышен лимит попыток.';
        }
        
        $userIP = $_SERVER['REMOTE_ADDR'];
        //$userPhone = '+7 (900) 000-00-00';
        //$userCoockie = 'coockie_id';
        $verifyConnectionHandler = new \api\VerifyConnection\VerifyConnectionHandler();

        $resultGetByIP = $verifyConnectionHandler->getByIP($userIP);
        $canSetIP = $verifyConnectionHandler->canSet($resultGetByIP);

        //$resultGetByPhone = $verifyConnectionHandler->getByIP($userPhone);
        //$canSetPhone = $verifyConnectionHandler->canSet($resultGetByPhone);

        if (!$canSetIP) $validate_errors = 'Попробуйте позже.';
        //if (!$canSetPhone) $validate_errors = 'Попробуйте позже';

        if (empty($validate_errors)) {
            $resultSetByIP = $verifyConnectionHandler->setByIP($userIP);
            //$resultSetByPhone = $verifyConnectionHandler->setByPhone($userPhone);
            //$resultSetByCoockie = $verifyConnectionHandler->setByCoockie($userCoockie);

            $object = $this->simpla->soap->generateObject($data);
            $response_soap = $this->simpla->soap->requestSoap($object, 'WebLK', 'GetPurchaseContracts');

            if (empty($response_soap['errors']) && !empty($response_soap['response'])) {
                $_SESSION['passport_user'] = [
                    'user_uid' => $response_soap['response'][0]['УИД_Клиента'],
                    'user_name' => $response_soap['response'][0]['Клиент'],
                ];

                foreach ($response_soap['response'] as $loan) {
                    $_SESSION['passport_user']['loans'][] = [
                        'loan_uid' => $loan['УИД'],
                        'loan_id' => $loan['НомерЗайма'],
                        'loan_type' => $loan['ТипДолга'],
                        'loan_amount' => $loan['Долг'],
                        'loan_date_added' => strtotime($loan['ДатаЗайма']),
                    ];
                }

                $response['result'] = $this->simpla->config->root_url . '/user_passport';
            } else {
                if (empty($response_soap['response'])) {
                    $response['errors'][] = 'Пользователь не найден, проверьте свои данные.';
                } else {
                    $response = $response_soap;
                }
            }

            $_SESSION['check_login_count'] = 0;
        } else {
            $response['errors'] = $validate_errors;
        }

        $this->response($response);
    }

    /**
     * Добавляем транзакцию
     * @param $data
     * @return void
     */
    private function setTransaction($data, $key_loan)
    {
        $data_transaction = [
            'payment_id' => $data['PaymentId'],
            'status' => $data['Status'],
            'amount' => $data['Amount'],
            'user_uid' => $_SESSION['passport_user']['user_uid'],
            'loan_uid' => $_SESSION['passport_user']['loans'][$key_loan]['loan_uid'],
            'mfo_agreement' => $data['OrderId'],
        ];
        $this->simpla->transactions->addPassportTransaction($data_transaction);
    }

    /**
     * Метод формирует ссылку на оплату
     * @return void
     */
    public function getPaymentLink()
    {
        $response = [];

        $amount = (float)$this->simpla->request->post('amount');
        $key_loan = (int)$this->simpla->request->post('key_loan');
        if ($amount > $_SESSION['passport_user']['loans'][$key_loan]['loan_amount']) {
            $response['error'] = 'Сумма превышает допустимую.';
        } else {
            $response = $this->simpla->tinkoff->init_payment_passport($amount);
            if ($response['Success']) {
                $this->setTransaction($response, $key_loan);
            }
        }

        $this->response($response);
    }
}
