<?php
ini_set('display_errors', 'on');
error_reporting(1);

ini_set('max_execution_time', '3600');
ini_set('memory_limit', '2048M');

chdir('..');
define('ROOT', dirname(__DIR__));
date_default_timezone_set('Europe/Moscow');

require 'api/Simpla.php';

/**
 * Класс для получения мессенджеров клиента из инфосферы
 */
class AddUserMessengers extends Simpla
{
    /** @var string[] Мессенджеры, которые нужно получить из инфосферы. См. весь список здесь https://i-sphere.ru/2.00/checkphone.php */
    private const MESSENGERS_TO_GET = [
//        'vk', // с 5-ю мессенджерами начинает тормозить crm
//        'ok',
        'skype',
        'whatsapp',
        'viber',
//        'mailru',
//        'twitter',
//        'facebook',
//        'instagram',
    ];

    /** @var string[] Названия полей из инфосферы, который нужно сохранить в s_user_data */
    private const INFOSPHERE_FIELDS_NAME_TO_SAVE = [
        'login' => 'login',
        'phone' => 'phone',
        'url_profile' => 'url',
        'url' => 'url',
        'link' => 'url'
    ];

    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 3500;

    private const LOG_FILE_NAME = 'add_user_messengers.txt';

    public function run()
    {
        // На всякий случай закрываем сессию, если была открыта ранее, так как crm начинал тормозить
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $executionTimeStart = microtime(true);

        $this->logging(__METHOD__, '', '', 'Начало работы крон: ' .
            (new DateTimeImmutable())->format('Y-m-d H:i:s'), self::LOG_FILE_NAME);

        // Включаем крон только на ночь
        if (date('H') > 6) {
            $this->logging(__METHOD__, '', '', 'Крон завершен, так как неподходящий час. Время: ' .
                (new DateTimeImmutable())->format('Y-m-d H:i:s'), self::LOG_FILE_NAME);
            return;
        }

        $dateFrom = (new DateTimeImmutable())->modify('-1 day')
            ->format('Y-m-d 00:00:00');

        $dateTo = (new DateTimeImmutable())->modify('-1 day')
            ->format('Y-m-d 23:59:59');

        $orders = $this->getIssuedOrders($dateFrom, $dateTo);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', '', 'Заявки не найдены', self::LOG_FILE_NAME);
            return;
        }

        $this->logging(__METHOD__, '', '', ['orders' => $orders], self::LOG_FILE_NAME);

        $usersData = $this->getUsersData($orders);

        foreach ($orders as $order) {

            // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
            if (microtime(true) - $executionTimeStart > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон. Время ' .
                    (new DateTimeImmutable())->format('Y-m-d H:i:s'), self::LOG_FILE_NAME);
                break;
            }

            // Если не указан номер телефона пользователя ИЛИ ранее уже получали данные пользователя, то заново не получаем
            if (empty($order->phone_mobile) || !empty($usersData[$order->user_id])) {
                continue;
            }

            try {
                $response = $this->infosphere->checkUserMessengers($order->phone_mobile, self::MESSENGERS_TO_GET);
                $this->logging(__METHOD__, '', '', ['request' => $order->phone_mobile, 'response' => $response], self::LOG_FILE_NAME);

                $this->saveUserMessengers($order, $response);
                sleep(10);
            } catch (Throwable $e) {
                $error = [
                    'Ошибка: ' . $e->getMessage(),
                    'Файл: ' . $e->getFile(),
                    'Строка: ' . $e->getLine(),
                    'Подробности: ' . $e->getTraceAsString()
                ];
                $this->logging(__METHOD__, '', '', ['result' => false, 'error' => $error], self::LOG_FILE_NAME);
            }
        }

        $this->logging(__METHOD__, '', '', 'Крон завершен: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE_NAME);
    }

    /**
     * @param stdClass $order
     * @param $response
     * @return void
     */
    private function saveUserMessengers(stdClass $order, $response): void
    {
        if (empty($response) || !empty($response['code'])) {
            return;
        }

        $addedMessengers = [];
        foreach ($response['Source'] as $source) {

            $source['Name'] = mb_strtolower($source['Name']);

            // Если мессенджер не найден
            if (empty($source['ResultsCount']) || empty($source['Record'])) {
                $this->saveNoCurrentMessenger($order, $source, $addedMessengers);
                continue;
            }

            // Мессенджеры, которые были найдены
            $addedMessengers[] = $source['Name'];

            // Пример ключа: has_whatsapp
            $keyToSave = 'has_' . $source['Name'];
            $this->user_data->set((int)$order->user_id, $keyToSave, '1');

            // В одноклассниках после $source['Record'] это простой массив, содержащий ключ 'Field'
            if (empty($source['Record']['Field'])) {
                $this->addOkMessenger($order, $source);
                continue;
            }

            // В viber после 'Field' идет не простой массив, а сразу ассоциативный массив с нужными значениями
            if (!empty($source['Record']['Field']['FieldName'])) {
                $this->addViberMessenger($order, $source);
                continue;
            }

            // В остальных после 'Field' идет простой массив, который нужно перебрать
            $this->addOtherMessengers($order, $source);
        }
    }

    /**
     * @param array $orders
     * @return array
     */
    private function getUsersData(array $orders): array
    {
        $usersId = array_column($orders, 'user_id');

        if (empty($usersId)) {
            return [];
        }

        $usersData = $this->user_data->getAll($usersId);

        if (empty($usersData) || !is_array($usersData)) {
            return [];
        }

        // Переиндексируем массив, ставим ключами user_id с сохранением значений элементов
        return array_column($usersData, null, 'user_id');
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    private function getIssuedOrders(string $dateFrom, string $dateTo): array
    {
        $query = $this->db->placehold('
            SELECT o.id AS order_id, o.user_id, c.issuance_date, u.phone_mobile
            FROM __orders o
            INNER JOIN __contracts c ON c.order_id = o.id
            INNER JOIN __users u ON u.id = o.user_id
            WHERE c.issuance_date >= ?
                AND c.issuance_date <= ?
                AND o.status = ?',
            $dateFrom,
            $dateTo,
            $this->orders::ORDER_STATUS_CRM_ISSUED
        );

        $this->db->query($query);
        $orders = $this->db->results();

        if (empty($orders) || !is_array($orders)) {
            return [];
        }

        return $orders;
    }

    private function saveNoCurrentMessenger(stdClass $order, array $source, array $addedMessengers)
    {
        // Сохраняем значение '0' у мессенджера только, если ранее мы его не добавляли
        if (!in_array($source['Name'], $addedMessengers)) {
            $keyToSave = 'has_' . $source['Name'];
            $this->user_data->set((int)$order->user_id, $keyToSave, '0');
        }
    }

    /**
     * @param stdClass $order
     * @param array $source
     * @return void
     */
    private function addOkMessenger(stdClass $order, array $source): void
    {
        foreach ($source['Record'] as $superField) {
            foreach ($superField['Field'] as $field) {

                $field['FieldName'] = mb_strtolower($field['FieldName']);

                if (isset(self::INFOSPHERE_FIELDS_NAME_TO_SAVE[$field['FieldName']])) {

                    // Пример ключа: ok_phone
                    $keyToSave = $source['Name'] . '_' . self::INFOSPHERE_FIELDS_NAME_TO_SAVE[$field['FieldName']];
                    $this->user_data->set((int)$order->user_id, $keyToSave, (string)$field['FieldValue']);
                }
            }
        }
    }

    /**
     * @param stdClass $order
     * @param array $source
     * @return void
     */
    private function addViberMessenger(stdClass $order, array $source): void
    {
        $source['Record']['Field']['FieldName'] = mb_strtolower($source['Record']['Field']['FieldName']);

        if (isset(self::INFOSPHERE_FIELDS_NAME_TO_SAVE[$source['Record']['Field']['FieldName']])) {

            // Пример ключа: viber_phone
            $keyToSave = $source['Name'] . '_' . self::INFOSPHERE_FIELDS_NAME_TO_SAVE[$source['Record']['Field']['FieldName']];
            $this->user_data->set((int)$order->user_id, $keyToSave, (string)$source['Record']['Field']['FieldValue']);
        }
    }

    /**
     * @param stdClass $order
     * @param array $source
     * @return void
     */
    private function addOtherMessengers(stdClass $order, array $source): void
    {
        foreach ($source['Record']['Field'] as $field) {

            $field['FieldName'] = mb_strtolower($field['FieldName']);

            if (isset(self::INFOSPHERE_FIELDS_NAME_TO_SAVE[$field['FieldName']])) {

                // Пример ключа: whatsapp_phone
                $keyToSave = $source['Name'] . '_' . self::INFOSPHERE_FIELDS_NAME_TO_SAVE[$field['FieldName']];
                $this->user_data->set((int)$order->user_id, $keyToSave, (string)$field['FieldValue']);
            }
        }
    }
}

$addUserMessengers = new AddUserMessengers();
$addUserMessengers->run();
