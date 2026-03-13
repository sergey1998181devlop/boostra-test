<?php

require_once('View.php');

/**
 * Class UserCreditDoctorView
 */
class UserCreditDoctorView extends View
{
    public function __construct()
    {
        parent::__construct();
        if (empty($this->user)) {
            header('Location: ' . $this->config->root_url . '/account/login');
            exit();
        }
    }

    function fetch()
    {
        // небольшая эмуляция контроллера
        $action = $this->request->get('action');
        if (!empty($action) && method_exists(self::class, $action)) {
            $this->$action();
        }

        $this->design->assign('order_items', $this->user_credit_doctor::ORDER_ITEMS);

        return $this->design->fetch('user_credit_doctor/user_credit_doctor.tpl');
    }

    /**
     * Получение карт пользователя
     * @return void
     */
    private function getUserCards()
    {
        $card_list = $this->notify->soap_get_card_list($this->user->uid);
        $cards = array();
        if ($card_list) {
            foreach ($card_list as $card) {
                if ($card->Status == 'A' && !empty($card->RebillId)) {
                    $card->expdate_formated = substr(chunk_split($card->ExpDate, 2, '/'), 0, -1);
                    $cards[] = $card;
                }
            }
        }

        if (!empty($cards)) {
            $cards[0]->checked = true;
        }

        $this->design->assign('cards', $cards);
        $this->request->html_output($this->design->fetch('user_credit_doctor/user_cards_list.tpl'));
    }

    /**
     * Сохранение или обновление квиза
     * @return void
     */
    private function addFormData()
    {
        $user_id = (int)$this->user->id;

        $data = $_POST;
        $data['user_id'] = $user_id;

        if ($this->user_credit_doctor->hasFormByUserId($user_id)) {
            $response['result'] = $this->user_credit_doctor->updateFormCreditDoctor($user_id, $data);
        } else {
            $response['result'] = $this->user_credit_doctor->addFormCreditDoctor($data);
        }

        $this->request->json_output($response);
    }

    /**
     * Отправка бесплатного урока
     * @return void
     */
    private function getFreeLesson()
    {
        $response = $this->saveEmail();
        if ($response['success']) {
            $order = (object)[
                'full_amount' => 0,
                'order_type_id' => 'Бесплатный урок',
                'user_id' => $response['user_id'],
                'email' => $response['email'],
            ];

            $this->uni_sender->sendMessageNewEmail($order);
            $response['message'] = 'Вам будет отправлено письмо с обучающим материалом';
        }

        $this->request->json_output($response);
    }

    /**
     * Отправка e-mail на окне об успешной оплате
     * @return void
     */
    private function sendFinishEmail()
    {
        $response = $this->saveEmail();
        if ($response['success']) {
            $payment = $this->user_credit_doctor->getLastPaymentByUserId($response['user_id']);

            $order = (object)[
                'full_amount' => $payment->full_amount,
                'order_type_id' => $payment->order_type_id,
                'user_id' => $response['user_id'],
                'email' => $response['email'],
            ];
            // отправим в UniSender
            $this->uni_sender->sendMessageNewEmail($order);
            $response['message'] = 'Вам будет отправлено письмо с обучающим материалом';
        }

        $this->request->json_output($response);
    }

    /**
     * Получение блока с СМС
     * @return void
     */
    private function getSmsBlock()
    {
        $this->request->html_output($this->design->fetch('user_credit_doctor/sms_block.tpl'));
    }

    /**
     * Получение блока с успешным текстом
     * @return void
     */
    private function getSuccessBlock()
    {
        $order_type_id = (int)$this->request->get('order_type_id');

        $this->design->assign('order_type_id', $order_type_id);
        $this->design->assign('href', $this->user_credit_doctor::ORDER_ITEMS[$order_type_id]['url']);

        $this->request->html_output($this->design->fetch('user_credit_doctor/success_block.tpl'));
    }

    /**
     * Валидация e-mail
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Сохраняет e-mail
     * @return array
     */
    private function saveEmail(): array
    {
        $result = [];
        $user_id = (int)$this->user->id;

        $data = $_POST;

        if (!self::validateEmail($data['email'])) {
            $result['error'] = 'E-mail отсутствует или введен не верно.';
        }

        if ($this->user_credit_doctor->hasUserEmail($data['email'], $user_id))
        {
            $result['error'] = 'Такой E-mail адрес уже используется.';
        }

        if (empty($result['error'])) {
            $result['success'] = $this->user_credit_doctor->updateFormCreditDoctor($user_id, $data);
            $result['email'] = $data['email'];
            $result['user_id'] = $user_id;
        }

        return $result;
    }

    /**
     * Формирует новый заказ c первым платежом на Кредитного Доктора
     * @return void
     */
    private function addPayment()
    {
        $response = [];

        $order_type_id = $this->request->post('order_type_id');

        $data = [
            'user_id' => (int)$_SESSION['user_id'],
            'full_amount' => $this->user_credit_doctor::ORDER_ITEMS[$order_type_id]['price'],
            'amount' => $this->user_credit_doctor::ORDER_ITEMS[$order_type_id]['price'],
            'filled' => $this->user_credit_doctor::ORDER_ITEMS[$order_type_id]['price'], // если автоплатежи, то пишем сколько первоначально
            'save_payment_method' => 0, // 1 - автоплатеж, помечаем так первый платеж например 1 руб.
            'status' => 'new',
            'order_type_id' => $order_type_id,
            'sms_code' => $_SESSION[$this->user_credit_doctor::SMS_SESSION_KEY],
        ];

        if($order_id = $this->user_credit_doctor->addCDPayment($data))
        {
            // отправляем запрос в yookassa
            $data['id'] = $order_id;
            $data['order_type_id'] = $order_type_id;
            $data['description'] = 'Оплата услуги Кредитный Доктор';

            $response['redirect_url'] = $this->yookassa_api->createPayment($data);
        }

        $this->request->json_output($response);
    }

    /**
     * Возвращает блок с ошибкой
     * @return void
     */
    private function getErrorBlock()
    {
        $this->request->html_output($this->design->fetch('user_credit_doctor/error_block.tpl'));
    }

    /**
     * Проверка формы при вводе СМС кода (чекбоксы)
     * @return void
     */
    private function validateSmsForm()
    {
        $response = [];

        $contract_main = $this->request->post('contract_main');
        $personal_distribution = $this->request->post('personal_distribution');
        $personal_processing = $this->request->post('personal_processing');


        if (empty($contract_main)) {
            $response['errors'][] = 'contract_main';
        }

        if (empty($personal_distribution)) {
            $response['errors'][] = 'personal_distribution';
        }

        if (empty($personal_processing)) {
            $response['errors'][] = 'personal_processing';
        }

        if (!empty($response['errors'])) {
            $response['errors_message'] = 'Примите все условия соглашения';
        }

        $this->request->json_output($response);
    }

    private function getPayment()
    {
        $response = [];

        $order_id = (int)$this->request->get('order_id');
        $payment = $this->user_credit_doctor->getCDPaymentById($order_id);

        if (!empty($payment->payment_id)) {
            $result = $this->yookassa_api->getPaymentStatus($payment->payment_id);
            $response['success'] = $result['isSuccess'];

            if ($response['success']) {
                $order_type_id = (int)$payment->order_type_id;
                $this->design->assign('order_type_id', $order_type_id);
                $this->design->assign('href', $this->user_credit_doctor::ORDER_ITEMS[$order_type_id]['url']);
                $response['message'] = $this->design->fetch('user_credit_doctor/success_block.tpl');
            }
        }

        if (empty($response['success'])) {
            $response['message'] = $this->design->fetch('user_credit_doctor/error_block.tpl');
        }

        $this->request->json_output($response);
    }
}
