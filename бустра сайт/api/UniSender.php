<?php

require_once( __DIR__ . '/../vendor/autoload.php');
require_once( __DIR__ . '/../api/Simpla.php');
require_once( __DIR__ . '/../api/unisender/api-wrapper/UnisenderApi.php');



use Unisender\ApiWrapper\UnisenderApi;

/**
 * Класс для отправки писем с помощью unisender
 * Class UniSender
 *
 * https://www.unisender.com/ru/support/api/common/bulk-email/
 */
class UniSender extends Simpla
{
    private $unisenderapi;
    const API_KEY = '6ezg983rih8ru3zhj5b8skhmsjoznpbf3aqnbn3a';

    /**
     * id списка для добавления контакта
     */
    const LIST_ID = 42;

    /**
     * id шаблока письма
     */
    const EMAIL_TEMPLATE_ID = 4318129;

    public function __construct()
    {
        $this->unisenderapi = new UnisenderApi(self::API_KEY);
    }

    /**
     * Поиск контакта по e-mail
     * @param string $email
     * @return false|string
     */
    public function getContact(string $email)
    {
        $response = $this->unisenderapi->getContact(['email' => $email]);
        return json_decode($response, true);
    }

    /**
     * Создание контакта
     * @param $data
     * @return mixed
     */
    public function importContacts($data)
    {
        $response = $this->unisenderapi->importContacts($data);
        return json_decode($response, true);
    }

    /**
     * Отправка сообщения
     * @param array $data
     * @return mixed
     */
    public function sendMessage(array $data)
    {
        $response = $this->unisenderapi->sendEmail($data);
        $this->logging(__METHOD__, 'https://www.unisender.com/', $data, $response, 'unisender.txt');
        return json_decode($response, true);
    }

    /**
     * Получение шаблона
     * @return mixed
     */
    public function getTemplate()
    {
        $response = $this->unisenderapi->getTemplate(['template_id' => self::EMAIL_TEMPLATE_ID, 'format' => 'html']);
        return json_decode($response, true);
    }

    /**
     * Функция отправки с логикой для новых пользователей
     * @param $order
     * @return void
     */
    public function sendMessageNewEmail($order)
    {
        $user = $this->users->get_user((int) $order->user_id);
        $user_from_unisender = $this->getContact($order->email);

        if (!empty($user_from_unisender['result'])) {
            $email_user = $user_from_unisender['result']['email']['email'];
        } else {
            $import_data = [
                'field_names' => [
                    'email',
                    'email_status',
                    'email_list_ids',
                    'Name',
                    'cd_type_id',
                    'cd_amount',
                    'tags',
                ],
                'data' => [
                    [
                        $order->email,
                        'new',
                        self::LIST_ID,
                        $user->firstname,
                        $order->order_type_id,
                        $order->full_amount,
                        'Кредитный Доктор',
                    ],
                ]
            ];
            $import_response = $this->importContacts($import_data);

            if ($import_response['result']['total'] === 1) {
                $email_user = $order->email;
            }
        }

        if (!empty($email_user)) {
            $response_template = $this->getTemplate();
            $email_send_data = [
                'email' => $email_user,
                'list_id' => self::LIST_ID,
                'sender_name' => 'Кредитный Доктор',
                'sender_email' => 'info@kreditoff-net.ru',
                'subject' => 'Курс избавления от долгов от Кредитного Доктора',
                'body' => $response_template['result']['body'],

            ];

            $this->sendMessage($email_send_data);
        }
    }
}
