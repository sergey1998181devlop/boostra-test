<?php

require_once( __DIR__ . '/../vendor/autoload.php');
require_once( __DIR__ . '/../api/Simpla.php');

/**
 * Class YooKassaApi
 * Класс для интеграции Кредитного Доктора
 *
 * Данный класс может расширяться и использоваться для других целей
 * Тестовые доступы в комментариях
 */
class YooKassaApi extends Simpla
{
    const SHOP_ID = 926434; //927219
    const SECRET_KEY = 'live_myhWZ8qYnWkaNxquW56dDIJZiPNURwSYA4Ly7Up67vs'; //test_YxRp5AHP5sBlTHjOBiRT9YDe9PZE3mpcaFqLcyUOj7c
    const REDIRECT_URI = 'https://www.boostra.ru/user/credit_doctor';

    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new \YooKassa\Client();
        $this->client->setAuth(self::SHOP_ID, self::SECRET_KEY);
    }

    /**
     * Создает автоплатеж
     * @param array $order
     * @return false|string
     * @throws \YooKassa\Common\Exceptions\ApiException
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException
     * @throws \YooKassa\Common\Exceptions\ExtensionNotFoundException
     * @throws \YooKassa\Common\Exceptions\ForbiddenException
     * @throws \YooKassa\Common\Exceptions\InternalServerError
     * @throws \YooKassa\Common\Exceptions\NotFoundException
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException
     */
    public function createRecurringPayment(array $order)
    {
        $data = [
            'amount' => [
                'value' => $order['amount'],
                'currency' => 'RUB',
            ],
            'capture' => true,
            'payment_method_id' => $order['payment_id'],
            'description' => $order['description'],
        ];

        $response = $this->client->createPayment($data, $order['id']);

        $this->logging(__METHOD__, 'yookassa.ru', $data, $response, 'yookassa.log');

        if($payment_id = $response->getId())
        {
            // добавим uid от yookassa к заказу и статус
            $data_payment = [
                'payment_id' => $payment_id,
                'status' => $response->getStatus(),
            ];
            $this->user_credit_doctor->updateCDPayment($order['id'], $data_payment);

            //получаем id нового платежа
            return $payment_id;
        }

        return false;
    }

    /**
     * Создание платежа
     * Создаем первый платеж, далее к нему прикрепляем автоплатежи
     * @param array $order
     * @return mixed
     */
    public function createPayment(array $order)
    {
        $data = [
            'amount' => [
                'value' => $order['amount'],
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => self::REDIRECT_URI . '?user_action=getPayment&order_id='  . $order['id'],
            ],
            'capture' => true,
            'description' => $order['description'],
            'metadata' => [
                'user_id' => $order['user_id'],
                'order_id' => $order['id'],
            ],
            //'save_payment_method' => true, // для автоплатежей
        ];

        $response = $this->client->createPayment($data, $order['id']);

        $this->logging(__METHOD__, 'yookassa.ru', $data, $response, 'yookassa.log');

        if($payment_id = $response->getId())
        {
            // добавим uid от yookassa к заказу и статус
            $data_payment = [
                'payment_id' => $payment_id,
                'status' => $response->getStatus(),
            ];
            $this->user_credit_doctor->updateCDPayment($order['id'], $data_payment);

            //получаем confirmationUrl для дальнейшего редиректа
            return $response->getConfirmation()->getConfirmationUrl();
        }

        return false;
    }

    /**
     * Приём уведомлений от yookassa для Кредитного Доктора
     * @return void
     */
    public function notificationPayments()
    {
        try {
            $source = file_get_contents('php://input');
            $data = json_decode($source, true);

            $factory = new \YooKassa\Model\Notification\NotificationFactory();
            $notificationObject = $factory->factory($data);
            $responseObject = $notificationObject->getObject();

            if (!$this->client->isNotificationIPTrusted($_SERVER['REMOTE_ADDR'])) {
                header('HTTP/1.1 400 Something went wrong');
                exit();
            }

            if (in_array($notificationObject->getEvent(), [
                \YooKassa\Model\NotificationEventType::PAYMENT_SUCCEEDED,
                \YooKassa\Model\NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE,
                \YooKassa\Model\NotificationEventType::PAYMENT_CANCELED,
                \YooKassa\Model\NotificationEventType::REFUND_SUCCEEDED
            ])) {

                $payment_id = $responseObject->getId();
                $payment_status = $responseObject->getStatus();

                $update_data = [
                    'status' => $payment_status,
                    'income_amount' => (float)$responseObject->getIncomeAmount()->getValue(),
                ];

                $payment = $this->user_credit_doctor->getCDPaymentByPaymentId($payment_id);
                $this->user_credit_doctor->updateCDPayment((int)$payment->id, $update_data);

                // если платеж подтвержден отправим чек
                if ($payment_status === \YooKassa\Model\PaymentStatus::SUCCEEDED) {
                    $payment->organization_id = 4;
                    $payment->Services = [$payment];
                    $this->cloudkassir->send_receipt($payment);

                    // если платеж первый, то выполним синхронизацию
                    $payment_method = $responseObject->getPaymentMethod();
                    if ($payment_method_id = $payment_method->getId()) {
                        if ($payment_id === $payment_method_id) {
                            // отправим в 1С
                            $user_uid_info = $this->users->get_user_uid($payment->user_id);
                            $data_soap = [
                                'Date' => (new \DateTime($payment->date_added))->format('YmdHis'),
                                'UID' => $user_uid_info->uid,
                                'Sum' => $payment->amount,
                                'ID_Operation' => $payment->payment_id,
                                'Service' => $this->user_credit_doctor::ORDER_ITEMS[(int)$payment->order_type_id]['description'],
                            ];
                            $object = $this->soap->generateObject($data_soap);
                            $this->soap->requestSoap($object, 'WebCRM', 'CredDoctor');

                            // отправим в AmoCrm
                            $this->amo_crm_api->addLid($payment);
                        } else {
                            // если автоплатёж, обновим информацию в основном платеже
                            $main_payment = $this->user_credit_doctor->getCDPaymentByPaymentId($payment_method_id);
                            // обновим заполненность платежа, при необходимости можно использовать поле filled - заполненость платежа, но нужна логика
                            $this->user_credit_doctor->updateCDPayment((int)$main_payment->id, ['filled' => $main_payment->amount + $payment->amount]);
                        }
                    }
                }
            } else {
                header('HTTP/1.1 400 Something went wrong');
                exit();
            }

        } catch (Exception $e) {
            header('HTTP/1.1 400 Something went wrong');
            exit();
        }
    }

    /**
     * Возвращает статус платежа и статус успеха
     * @param $payment_id
     * @return array
     * @throws \YooKassa\Common\Exceptions\ApiException
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException
     * @throws \YooKassa\Common\Exceptions\ExtensionNotFoundException
     * @throws \YooKassa\Common\Exceptions\ForbiddenException
     * @throws \YooKassa\Common\Exceptions\InternalServerError
     * @throws \YooKassa\Common\Exceptions\NotFoundException
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException
     */
    public function getPaymentStatus($payment_id): array
    {
        $payment_info = $this->client->getPaymentInfo($payment_id);
        return [
            'status' => $payment_info->getStatus(),
            'isSuccess' => $payment_info->getStatus() === \YooKassa\Model\PaymentStatus::SUCCEEDED,
        ];
    }
}
