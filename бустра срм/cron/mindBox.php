<?php
date_default_timezone_set('Europe/Moscow');

// Подключаем Composer autoload до Simpla — иначе классы Mindbox\* не находятся при запуске из cron
$autoload = dirname(__FILE__) . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Composer autoload not found: {$autoload}. Run: composer install\n");
    exit(1);
}
require_once $autoload;

require_once dirname(__FILE__) . '/../api/Simpla.php';
if (file_exists(dirname(__FILE__) . '/../lib/autoloader.php')) {
    require_once dirname(__FILE__) . '/../lib/autoloader.php';
}

use Mindbox\DTO\V3\OperationDTO;
use Mindbox\Mindbox;
use App\Repositories\MindboxDbRepository;
use App\Enums\MindboxConstants;
use api\helpers\TimeZoneHelper;

/**
 * Класс для обработки данных и отправки в Mindbox
 */
class MindBoxCron extends Simpla
{
    private Mindbox $mindbox;
    private MindboxDbRepository $dbRepo;

    public function __construct()
    {
        parent::__construct();
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ALL);

        $this->mindbox = $this->mindboxApi->createMindbox();

        $this->dbRepo = new MindboxDbRepository($this->db);
    }

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        try {
            foreach (MindboxConstants::USER_OPERATIONS as $operation) {
                $this->processUserBatch($operation);
            }

            foreach (MindboxConstants::ORDER_OPERATIONS as $operation) {
                $this->processOrderBatch($operation);
            }
        } catch (Throwable $e) {
            $this->logging('MindboxCron Fatal Error', $e->getMessage(), [], ['trace' => $e->getTraceAsString()], 'mindbox_errors.txt');
            throw $e;
        }
    }

    /**
     * Обработка батча пользовательских операций.
     * @param string $operationType
     * @return void
     */
    private function processUserBatch(string $operationType): void
    {
        echo "Начинаем обработку операций типа: $operationType\n";

        $records = $this->user_data->getRecords($operationType, 0, MindboxConstants::BATCH_SIZE_USER);
        if (!$records) {
            echo "Не найдены записи типа $operationType для отправки.\n";
            return;
        }

        $this->user_data->updateRecords(
            array_map(fn($r) => ['user_id' => $r->user_id, 'key' => $r->key], $records),
            1
        );

        $usersData = $this->dbRepo->getUserDataBatch(array_column($records, 'user_id'));

        $results = [];
        foreach ($records as $record) {
            $results[] = $this->processSingleUserRecord($record, $usersData, $operationType);
        }

        $this->handleBatchResults($results, 'user_data');

        echo "Завершена обработка операций типа: $operationType\n";
    }

    /**
     * Обработка одной пользовательской записи.
     * @param object $record
     * @param array $usersData
     * @param string $operationType
     * @return array ['to_delete' => [...], 'to_revert' => [...], 'message' => '...']
     */
    private function processSingleUserRecord(object $record, array $usersData, string $operationType): array
    {
        $recordInfo = $this->getMarkerInfo($record, 'user_id');
        $result = ['to_delete' => [], 'to_revert' => [], 'message' => ''];

        if (!isset($usersData[$record->user_id])) {
            $result['to_delete'][] = $recordInfo;
            $result['message'] = "Пользователь не найден - user_id: {$record->user_id}\n";
            return $result;
        }

        try {
            $this->executeUserOperation($usersData[$record->user_id], $operationType);
            $result['to_delete'][] = $recordInfo;
            $result['message'] = "Успешно обработан - user_id: {$record->user_id}, key: {$record->key}\n";
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'уже существует')) {
                $result['to_delete'][] = $recordInfo;
                $result['message'] = "Пользователь был добавлен ранее - user_id: {$record->user_id}\n";
            } elseif (str_contains($e->getMessage(), 'Невозможно однозначно определить клиента')) {
                $result['to_delete'][] = $recordInfo;
                $result['message'] = "Невозможно однозначно определить клиента - user_id: {$record->user_id}\n";
            } else {
                $result['to_revert'][] = $recordInfo;
                $result['message'] = "Ошибка - user_id: {$record->user_id}, error: {$e->getMessage()}\n";
                $this->logging(
                    'MindboxCron Error',
                    "Operation: {$operationType}, User ID: {$record->user_id}",
                    [
                        'operation_type' => $operationType,
                        'user_id' => $record->user_id,
                    ],
                    [
                        'error_message' => $e->getMessage(),
                    ],
                    'mindbox_errors.txt'
                );
            }
        }
        return $result;
    }

    /**
     * Обработка батча заказов.
     * @param string $operationType
     * @return void
     */
    private function processOrderBatch(string $operationType): void
    {
        echo "Начинаем обработку операций типа: $operationType\n";

        $records = $this->order_data->getRecords($operationType, 0, MindboxConstants::BATCH_SIZE_ORDER);
        if (!$records) {
            echo "Не найдены записи типа $operationType для отправки.\n";
            return;
        }

        $this->order_data->updateRecords(
            array_map(fn($r) => ['order_id' => $r->order_id, 'key' => $r->key], $records),
            1
        );

        $orderIds = array_column($records, 'order_id');
        $ordersData = $this->dbRepo->getOrdersDataBatch($orderIds);
        $addonsData = $this->dbRepo->getOrderAddonsBatch($orderIds);

        $results = [];
        foreach ($records as $record) {
            $results[] = $this->processSingleOrderRecord($record, $ordersData, $addonsData, $operationType);
        }

        $this->handleBatchResults($results, 'order_data');

        echo "Завершена обработка операций типа: $operationType\n";
    }

    /**
     * Обработка одной заказной записи.
     * @param object $record
     * @param array $ordersData
     * @param array $addonsData
     * @param string $operationType
     * @return array ['to_delete' => [...], 'to_revert' => [...], 'message' => '...']
     */
    private function processSingleOrderRecord(object $record, array $ordersData, array $addonsData, string $operationType): array
    {
        $recordInfo = $this->getMarkerInfo($record, 'order_id');
        $result = ['to_delete' => [], 'to_revert' => [], 'message' => ''];

        if (!isset($ordersData[$record->order_id])) {
            $result['to_delete'][] = $recordInfo;
            $result['message'] = "Заказ не найден - order_id: {$record->order_id}\n";
            return $result;
        }

        try {
            $order = $ordersData[$record->order_id];
            $addons = $addonsData[$record->order_id] ?? [];
            $this->sendOrderToMindbox($order, $addons, $operationType);
            $result['to_delete'][] = $recordInfo;
            $result['message'] = "Успешно отправлен заказ - order_id: {$record->order_id}\n";
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'cодержится в черном списке')) {
                $result['to_delete'][] = $recordInfo;
                $result['message'] = "Пользователь в ч/с - order_id: {$record->order_id}\n";
            } else {
                $result['to_revert'][] = $recordInfo;
                $result['message'] = "Ошибка при отправке заказа - order_id: {$record->order_id}, error: {$e->getMessage()}\n";
                $this->logging(
                    'MindboxCron Order Error',
                    "Order ID: {$record->order_id}",
                    ['order_id' => $record->order_id],
                    ['error_message' => $e->getMessage()],
                    'mindbox_orders_errors.txt'
                );
            }
        }
        return $result;
    }

    /**
     * Обработка результатов батча (удаление/откат маркеров, вывод сообщений).
     * @param array $results
     * @param string $dataType 'user_data' или 'order_data'
     * @return void
     */
    private function handleBatchResults(array $results, string $dataType): void
    {
        $toRevert = [];
        $toDelete = [];
        $messages = [];

        foreach ($results as $res) {
            $toRevert = array_merge($toRevert, $res['to_revert']);
            $toDelete = array_merge($toDelete, $res['to_delete']);
            $messages[] = $res['message'];
        }

        // Вывод сообщений
        foreach ($messages as $msg) {
            echo $msg;
        }

        // Откатываем неуспешные
        if ($dataType === 'user_data') {
            $toRevert = array_values(array_filter($toRevert, function ($r) {
                return is_array($r) && isset($r['user_id'], $r['key']) && $r['user_id'] !== null && $r['key'] !== null;
            }));
        } elseif ($dataType === 'order_data') {
            $toRevert = array_values(array_filter($toRevert, function ($r) {
                return is_array($r) && isset($r['order_id'], $r['key']) && $r['order_id'] !== null && $r['key'] !== null;
            }));
        }
        if ($toRevert) {
            if ($dataType === 'user_data') {
                $this->user_data->updateRecords($toRevert, 0);
            } elseif ($dataType === 'order_data') {
                $this->order_data->updateRecords($toRevert, 0);
            }
            echo "Откачено записей: " . count($toRevert) . "\n";
        }

        // Удаляем успешные
        if ($dataType === 'user_data') {
            $toDelete = array_values(array_filter($toDelete, function ($r) {
                return is_array($r) && isset($r['user_id'], $r['key'], $r['value']) && $r['user_id'] !== null && $r['key'] !== null;
            }));
        } elseif ($dataType === 'order_data') {
            $toDelete = array_values(array_filter($toDelete, function ($r) {
                return is_array($r) && isset($r['order_id'], $r['key'], $r['value']) && $r['order_id'] !== null && $r['key'] !== null;
            }));
        }
        if ($toDelete) {
            if ($dataType === 'user_data') {
                $this->user_data->deleteRecords($toDelete);
            } elseif ($dataType === 'order_data') {
                $this->order_data->deleteRecords($toDelete);
            }
            echo "Удалено записей: " . count($toDelete) . "\n";
        }
    }

    /**
     * Формирует информацию о маркере для удаления/отката.
     * @param object $record
     * @param string $idField
     * @return array
     */
    private function getMarkerInfo(object $record, string $idField): array
    {
        $idValue = $record->$idField;
        return [
            $idField => $idValue,
            'key' => $record->key,
            'value' => 1,
        ];
    }

    /**
     * Отправка заказа с допами в Mindbox
     * @param object $order
     * @param array $addons
     * @param string $operationType
     * @return void
     */
    private function sendOrderToMindbox(object $order, array $addons, string $operationType): void
    {
        // Проверка даты заказа - пропускаем старые заказы и некорректные даты, заказы без пользователей
        try {
            $cutoffDate = new DateTime(MindboxConstants::START_DATE_ORDERS);
            $orderDate = new DateTime($order->date);
            if (($orderDate < $cutoffDate) || !$order->phone_mobile) {
                return;
            }
        } catch (Exception $e) {
            return;
        }

        // Формируем позиции заказа (линии продуктов)
        $orderLines = $this->buildOrderLines($order, $addons);

        $amountPayments = MindboxConstants::shouldIncludePayments($order->{'1c_status'}) ? (int)$order->amount_payments : 0;

        $data = [
            'customer' => [
                'mobilePhone' => (string)$order->phone_mobile,
            ],
            'order' => [
                'ids' => [
                    'BoostraID' => (string)$order->order_id,
                ],
                'customFields' => [
                    'amountofpayments' => $amountPayments,
                    'decisiondate' => $this->formatDate($order->confirm_date),
                    'interest' => $order->percent,
                    'requestedAmount' => $order->req_amount,
                    'orderId' => $order->order_id,
                    'sOrdersReasonId' => $order->reason_id,
                    'sOrdersAmount' => $order->approve_amount,
                    'sOrdersPeriod' => $order->period,
                    'sOrdersDate' => $this->formatDate($order->date),
                    'sOrdersStatus' => $order->{'1c_status'},
                    'statusCRM' => $order->status,
                    'sOrdersUtmSource' => $order->utm_source,
                    'ordersutmmedium' => $order->utm_medium,
                    'ordersutmcampaign' => $order->utm_campaign,
                    'orderswebmasterID' => $order->webmaster_id,
                    'dop' => (bool)$order->addition_services,
                    'contractsissuancedate' => $this->formatDate($order->issuance_date),
                    'contractsclosedate' => $this->formatDate($order->close_date),
                    'pdnnbki' => $order->pdn_nkbi,
                    'scoristaball' => $order->scorista_ball,
                    'nomerzayavki1s' => $order->{'1c_id'},
                    'firstloan' => (bool)$order->first_loan,
                    'orderNumber' => $order->contract,
                ],
                'lines' => $orderLines
            ]
        ];

        // Получаем операцию и флаг синхронности
        $operationConfig = MindboxConstants::ORDER_OPERATION_MAP[$operationType];
        $operation = $operationConfig['operation'];
        $sync = $operationConfig['sync'];

        $this->sendToMindbox($operation, $data, $sync);
    }

    /**
     * Построение линий заказа
     * @param object $order
     * @param array $addons
     * @return array
     */
    private function buildOrderLines(object $order, array $addons): array
    {
        $loanStatus = MindboxConstants::getOrderStatus($order->{'1c_status'});
        $lines = [
            [
                'lineId' => 1,
                'product' => ['ids' => ['likeZaim67' => MindboxConstants::ORDER_LINE_MAP['loan_body']]],
                'status' => $loanStatus,
                'basePricePerItem' => (int)$order->body_sum,
                'quantity' => 1,
                'customFields' => [
                    'ReturnAmount' => 0,
                    'Startdate' => $this->formatDate($order->date),
                    'status' => $order->status,
                ],
            ]
        ];

        $addonLines = array_filter($addons, function ($addon) {
            return isset(MindboxConstants::ORDER_LINE_MAP[$addon->service_type]);
        });

        foreach ($addonLines as $index => $addon) {
            $status = MindboxConstants::getAddonStatus($addon->status, $loanStatus);

            $lines[] = [
                'lineId' => $index + 2,
                'product' => ['ids' => ['likeZaim67' => MindboxConstants::ORDER_LINE_MAP[$addon->service_type]]],
                'basePricePerItem' => (int)$addon->amount,
                'quantity' => 1,
                'status' => $status,
                'customFields' => [
                    'licenseNumber' => $addon->license_key,
                    'ReturnAmount' => $addon->return_amount,
                    'Enddate' => $this->formatDate($addon->return_date),
                    'Startdate' => $this->formatDate($addon->date_added),
                    'status' => $addon->status,
                ],
            ];
        }

        return $lines;
    }

    /**
     * Форматирование даты из БД (московское время) в ISO 8601 UTC с суффиксом Z.
     * Явно интерпретируем входящую дату как Europe/Moscow и конвертируем в UTC,
     * чтобы Mindbox не применял часовой пояс повторно при отображении.
     *
     * @param string|null $date
     * @return string|null
     */
    private function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            $dt = new DateTime($date, new DateTimeZone('Europe/Moscow'));
            $dt->setTimezone(new DateTimeZone('UTC'));
            return $dt->format('Y-m-d\TH:i:s\Z');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param object $user
     * @param string $operation
     * @return void
     */
    private function executeUserOperation(object $user, string $operation): void
    {
        $config = MindboxConstants::USER_OPERATION_MAP[$operation];
        $method = $config['operation'];
        $sync = $config['sync'];

        if (!method_exists($this, $method)) {
            return;
        }

        // Проверяем существование клиента в Mindbox
        $existingCustomer = $this->mindboxApi->isExistCustomer($user->phone_mobile);
    
        // Клиент не существует в Mindbox
        if ($existingCustomer === false) {
            // регистрируем и обрабатываем ситуацию, когда клиент оч старый, и он авторизуется, а в майнде его нет, поэтому тоже региструруем
            if ($method === 'registerCustomer' || $method === 'authorizeCustomer') {
                $this->registerCustomer($user, $sync);
            }
            return;
        }

        // Для операций с клиентом определяем метод по наличию ID
        if ($method === 'registerCustomer' || $method === 'editCustomer') {
            $this->editCustomer($user, $sync);
        } elseif ($method === 'authorizeCustomer') {
            // Авторизация работает для всех
            $this->authorizeCustomer($user, $sync);
        }
    }

    /**
     * @param object $user
     * @param bool $sync
     * @return void
     */
    private function registerCustomer(object $user, bool $sync = true): void
    {
        $data = [
            'customer' => array_filter([
                'birthDate' => $user->birth,
                'lastName' => $user->lastname,
                'firstName' => $user->firstname,
                'middleName' => $user->patronymic,
                'email' => $user->email,
                'mobilePhone' => $user->phone_mobile,
                'customFields' => [
                    'phoneconfirmed' => 1,
                    'boostraCreated' => $this->formatDate($user->created),
                ],
                'ids' => ['boostraClientID' => (string)$user->id],
                'subscriptions' => $this->buildSubscriptions(),
            ])
        ];

        $this->sendToMindbox('Boostra.Website.RegisterCustomer', $data, $sync);
    }



    /**
     * @param object $user
     * @param bool $sync
     * @return void
     */
    private function editCustomer(object $user, bool $sync = false): void
    {
        $loans = json_decode($user->quantity_loans, true);

        $parsed = TimeZoneHelper::parseRegion($user->Regregion ?? '');
        $timeZone = $parsed !== [] ? $parsed[1] : TimeZoneHelper::getTimezoneByRegionCode($user->factual_region_code ?? null);

        $data = [
            'customer' => [
                'birthDate' => $user->birth,
                'sex' => $user->gender,
                'timeZone' => $timeZone,
                'lastName' => $user->lastname,
                'firstName' => $user->firstname,
                'middleName' => $user->patronymic,
                'email' => $user->email,
                'mobilePhone' => $user->phone_mobile,
                'customFields' => [
                    'boostraCreated' => $this->formatDate($user->created),
                    'phoneconfirmed' => 1,
                    'regregion' => $user->Regregion,
                    'bankrupt' => $user->bankrupt,
                    'moratoriumDate' => $this->formatDate($user->maratorium_date),
                    'partnerName' => $user->partner_name,
                    'boostraUTMsource' => $user->utm_source,
                    'boostraUTMmedium' => $user->utm_medium,
                    'boostraUTMcampaign' => $user->utm_campaign,
                    'boostraUTMcontent' => $user->utm_content,
                    'boostraUTMterm' => $user->utm_term,
                    'boostraCardConfirmed' => $user->card_added,
                    'boostraCardAddedDate' => $this->formatDate($user->card_added_date),
                    'boostraAcceptDataAdded' => $user->accept_data_added,
                    'boostraAcceptDataAddedDate' => $this->formatDate($user->accept_data_added_date),
                    'boostraSoglasienaPDn' => $user->personal_data_added,
                    'boostraPersonalDataAddedDate' => $this->formatDate($user->personal_data_added_date),        
                ],
                'ids' => ['boostraClientID' => $user->id],
                'subscriptions' => $this->buildSubscriptionsWithStatus(!$user->block_sms_created_at),
            ]
        ];

        $this->sendToMindbox('Boostra.Website.EditCustomer', $data, $sync);
    }

    /**
     * @param object $user
     * @param bool $sync
     * @return void
     */
    private function authorizeCustomer(object $user, bool $sync = true): void
    {
        $data = [
            'customer' => [
                'email' => $user->email,
                'mobilePhone' => $user->phone_mobile,
                'ids' => ['boostraClientID' => (string)$user->id],
            ]
        ];

        $this->sendToMindbox('Boostra.Website.AuthorizeCustomer', $data, $sync);
    }

    /**
     * @return array
     */
    private function buildSubscriptions(): array
    {
        $points = ['SMS', 'Webpush', 'Mobilepush', 'Email'];
        return array_map(fn($point) => [
            'brand' => $this->config->mb_brand,
            'pointOfContact' => $point,
        ], $points);
    }

    /**
     * @param bool $isSubscribed
     * @return array
     */
    private function buildSubscriptionsWithStatus(bool $isSubscribed): array
    {
        $points = ['SMS', 'Webpush', 'Mobilepush'];
        return array_map(fn($point) => [
            'brand' => $this->config->mb_brand,
            'pointOfContact' => $point,
            'isSubscribed' => $isSubscribed,
        ], $points);
    }

    /**
     * @param string $operation
     * @param array $data
     * @param bool $sync
     * @return void
     */
    private function sendToMindbox(string $operation, array $data, bool $sync = false): void
    {
        $body = new OperationDTO($data);
        $this->mindbox->getClientV3()
            ->prepareRequest('POST', $operation, $body, '', [], $sync, false)
            ->sendRequest();
    }
}

$cron = new MindBoxCron();
$cron->run();