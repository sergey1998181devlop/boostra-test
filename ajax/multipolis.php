<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');
require_once 'api/Simpla.php';
require_once 'api/TelegramApi.php';

/**
 * Class Multipolis
 * Обработка формы с мультиполисом
 */
class MultipolisAjax extends Simpla
{

    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    const KEYS_MESSAGE = [
        'Фамилия',
        'Имя',
        'Отчество',
        'Телефон',
        'Номер консьерж сервиса',
        'Id консьерж сервиса в БД'
    ];

    public function run()
    {
        $response = [];
        $data = [];

        $data['lastname'] = $this->request->post('lastname');
        $data['firstname'] = $this->request->post('firstname');
        $data['patronymic'] = $this->request->post('patronymic');
        $data['phone_mobile'] = $this->request->post('phone_mobile');
        $data['multipolis_number'] = $this->request->post('multipolis_number');
        $data['multipolis_id'] = $this->request->post('multipolis_id', 'integer');

        $errors = $this->validate($data);

        if (!empty($errors)) {
            $response['errors'] = $errors;
        } else {

            // отправим уведомление на почту через SMTP
            $message = "<h3>Уведомление об отправке формы!</h3> \n";
            $message_array = array_combine(self::KEYS_MESSAGE, $data);

            foreach ($message_array as $key => $item) {
                $message .= "<p><b>$key:</b> <i>$item</i></p>";
            }

            $response_mail = $this->multipolis->sendNoticeEmail($message);

            if (!empty($response_mail)) {
                $response['success'] = $this->multipolis->addMultipolisForm($data);
                $this->multipolis->updateItem($data['multipolis_id'], ['is_sent' => 1]);

                $this->design->assign('title', 'Ваша заявка отправлена');
                $this->design->assign('description', 'Наш консультант свяжется с Вами в течении 30 минут.');
                $response['message'] = $this->design->fetch('block/modal_success_result.tpl');
            }
        }

        $this->request->json_output($response);
    }

    /**
     * Валидация формы
     * @param $data
     * @return array
     */
    private function validate($data): array
    {
        $errors = [];

        $filter_array = array_filter($data, function ($item){
            return empty($item);
        });

        if (!empty($filter_array)) {
            foreach (array_keys($filter_array) as $key) {
                $errors[$key] = 'Заполните поле.';
            }
        }

        return $errors;
    }
}

new MultipolisAjax;
