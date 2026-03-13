<?php

namespace App\Http\Controllers;

use api\handlers\ChangeProlongationHandler;
use App\Core\Application\Facades\DB;
use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Handlers\AdditionalServicesHandler;
use App\Handlers\UserDncCallsHandler;
use App\Handlers\ExtraServiceLicenseKeyHandler;
use App\Models\Order;
use App\Models\OrderData;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;
use App\Service\ChangelogService;
use App\Service\OrderStatusSyncService;
use DateTime;
use Throwable;

require_once __DIR__ . '/../../../api/handlers/ChangeProlongationHandler.php';

class OrderController
{
    private OrderStatusSyncService $syncService;
    private UserDncCallsHandler $robotCallsHandler;

    public function __construct(
        OrderStatusSyncService       $syncService,
        UserDncCallsHandler          $robotCallsHandler
    ) {
        $this->syncService = $syncService;
        $this->robotCallsHandler = $robotCallsHandler;
    }

    /**
     * Переключение кредитного доктора при выдаче займа
     * @param Request $request
     * @return Response
     */
    public function toggleCreditDoctor(Request $request): Response
    {
        $orderId = $request->getParam('id');
        if (!is_numeric($orderId)) {
            return response()->json(['message' => 'Неверный ID заявки'], 400);
        }

        $orderDataModel = new OrderData();
        $key = 'disable_additional_service_on_issue';

        $orderData = $orderDataModel->get(
            ['order_id', 'key', 'value'],
            [
                'order_id' => (int)$orderId,
                'key' => $key
            ]
        )->getData();

        $result = empty($orderData)
            ? $orderDataModel->insert([
                'order_id' => (int)$orderId,
                'key' => $key,
                'value' => 1
            ])
            : $orderDataModel->update(
                ['value' => !$orderData['value']],
                ['order_id' => (int)$orderId, 'key' => $key]
            );

        if (!$result) {
            return response()->json(['message' => 'Ошибка при переключении программного обеспечения'], 500);
        }

        if (!empty($request->input('manager_id')) && !empty($request->input('user_id'))) {
            $changelogService = new ChangelogService();
            $changelogService->addLog(
                (int)$request->input('manager_id'),
                $key,
                'Включение',
                'Выключение',
                (int)$orderId,
                (int)$request->input('user_id')
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Кредитный доктор переключен'
        ]);
    }

    public function disableAdditionalServicesByList(Request $request): Response
    {
        $orderDataModel = new OrderData();
        $changelogService = new ChangelogService();
        
        $defaultServices = [
            ...$orderDataModel::getAdditionalServiceList(),
            ...$orderDataModel::getHalfAdditionalServiceList()
        ];
        
        $requestedServices = $request->input('services') ?? $request->json('services');
        
        // Если services не передан, пустой массив или содержит только пустые строки - используем дефолт
        if (!isset($requestedServices) || empty($requestedServices)) {
            $services = $defaultServices;
        } elseif (is_array($requestedServices)) {
            $filteredServices = array_filter($requestedServices, function($s) {
                return !empty($s);
            });
            $services = empty($filteredServices) ? $defaultServices : $requestedServices;
        } else {
            $services = $requestedServices;
        }

        $referenceNumber = $request->getParam('contract');
        $managerId = (int)($request->input('manager_id') ?? 360);
        $contract = DB::db()->get('s_contracts (contract)',
            ['[>]s_orders (order)' => ['contract.order_id' => 'id']],
            [
                'contract.order_id',
                'contract.user_id',
                'contract.number',
            ],
            ['number' => $referenceNumber]
        );

        if (empty($contract) || empty($contract['order_id'])) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Ошибка при отключении программного обеспечения',
                ],
                500
            );
        }

        $disabled = 0;
        foreach ($services as $service) {
            $orderData = $orderDataModel->get(
                ['order_id', 'key', 'value'],
                [
                    'order_id' => (int)$contract['order_id'],
                    'key' => $service
                ]
            )->getData();

            if (isset($orderData['value']) && $orderData['value'] == 1) {
                continue;
            }

            $result = (empty($orderData))
                ? $orderDataModel->insert(
                    [
                        'order_id' => (int)$contract['order_id'],
                        'key' => $service,
                        'value' => 1
                    ]
                )
                : $orderDataModel->update(
                    ['value' => 1],
                    ['order_id' => (int)$contract['order_id'], 'key' => $service]
                );

            if (!$result) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Ошибка при отключении программного обеспечения ' . $service,
                    ],
                    500
                );
            } else {
                $disabled++;
                $changelogService->addLog(
                    $managerId,
                    $service,
                    'Включение',
                    'Выключение',
                    (int)$contract['order_id'],
                    (int)$contract['user_id']
                );
            }
        }

        if (!$disabled) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Нет программного обеспечения для отключения',
                ],
                500
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Программное обеспечение отключено'
        ]);
    }

    public function disableAdditionalServices(Request $request): Response
    {
        $orderId = $request->getParam('id');
        $managerId = (int)($request->input('manager_id') ?? 360);

        $services = [
            AdditionalServiceKey::HALF_REPAYMENT()->getValue(),
            AdditionalServiceKey::REPAYMENT()->getValue(),
            AdditionalServiceKey::HALF_PARTIAL_REPAYMENT()->getValue(),
            AdditionalServiceKey::PARTIAL_REPAYMENT()->getValue(),
            AdditionalServiceKey::ON_ISSUE()->getValue(),
            AdditionalServiceKey::SO_REPAYMENT()->getValue(),
            AdditionalServiceKey::HALF_SO_REPAYMENT()->getValue(),
            AdditionalServiceKey::SO_PARTIAL_REPAYMENT()->getValue(),
            AdditionalServiceKey::HALF_SO_PARTIAL_REPAYMENT()->getValue()
        ];

        $orderModel = new Order();
        $orderDataModel = new OrderData();
        $changelogService = new ChangelogService();
        $errors = [];

        $existingData = $orderDataModel->select(['order_id', 'key', 'value'], [
            'order_id' => $orderId,
            'key' => $services
        ])->getData();

        $existingDataMap = [];
        foreach ($existingData as $data) {
            $existingDataMap[$data['key']] = $data['value'];
        }

        foreach ($services as $key) {
            $oldValue = $existingDataMap[$key] ?? null;

            if (!isset($oldValue)) {
                $result = $orderDataModel->insert([
                    'order_id' => $orderId,
                    'key' => $key,
                    'value' => 1 // Отключено
                ]);
            } else {
                $result = $orderDataModel->update(
                    ['value' => 1], // Обновляем статус на отключено
                    ['order_id' => $orderId, 'key' => $key]
                );
            }

            if (!$result->getData()) {
                $errors[] = "Ошибка при обработке программного обеспечения: {$key}";
                continue;
            }

            // Лог об отключении создаётся только в случае, если программное обеспечение было ранее включено
            if ($oldValue != 1) {
                $order = $orderModel->select(['user_id'], [
                    'id' => (int)$orderId,
                ])->getData();

                $userId = null;
                if ($order && !empty($order[0]) && !empty($order[0]['user_id'])) {
                    $userId = $order[0]['user_id'];
                }

                $changelogService->addLog(
                    $managerId,
                    $key,
                    'Включение',
                    'Выключение',
                    $orderId,
                    $userId
                );
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Кэш уже сброшен',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Кэш сброшен'
        ]);
    }

    public function switchProlongation(Request $request): Response
    {
        $orderID = $request->input('orderID') ?? $request->json('orderID');
        $managerID = $request->input('managerID') ?? $request->json('managerID');
        $value = $request->input('value') ?? $request->json('value');
        $contractNumber = $request->input('contractNumber') ?? $request->json('contractNumber');

        if (empty($orderID) && !empty($contractNumber)) {
            $orderID = (new Order())->get(
                ["[>]s_contracts" => ["id" => "order_id"]],
                ["s_orders.id"],
                ["s_contracts.number" => $contractNumber]
            )->getData()['id'] ?? null;
        }

        if (empty($orderID) || empty($managerID) || !isset($value)) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно параметров'
            ], 400);
        }

        try {
            $result = (new ChangeProlongationHandler())->handle($orderID, $managerID, $value);

            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getExtraServiceLicenseKey(Request $request): Response
    {
        $contractNumber = $request->input('contract_number') ?? $request->json('contract_number');
        $serviceType = $request->input('service_type') ?? $request->json('service_type');

        if (empty($contractNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан номер договора'
            ], 400);
        }

        if (empty($serviceType)) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан тип программного обеспечения'
            ], 400);
        }

        $handler = new ExtraServiceLicenseKeyHandler();
        $result = $handler->getLicenseKey($contractNumber, $serviceType);

        $httpCode = $result['http_code'] ?? 200;
        unset($result['http_code']);

        return response()->json($result, $httpCode);
    }

    public function getRecompenseAdditionalServices(Request $request): Response
    {
        $orderId = (int)($request->input('order_id') ?? $request->json('order_id'));
        $contractNumber = $request->input('contract_number') ?? $request->json('contract_number');
        $serviceType = $request->input('service_type') ?? $request->json('service_type');

        if (empty($orderId) && empty($contractNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно параметров'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty($orderId) && !empty($contractNumber)) {
            $contractNumber = formatReferenceNumber($contractNumber);
            $orderId = (new Order())->get(
                ["[>]s_contracts" => ["id" => "order_id"]],
                ["s_orders.id"],
                ["s_contracts.number" => $contractNumber]
            )->getData()['id'] ?? null;
        }

        if (empty($orderId)) {
            return response()->json([
                'success' => false,
                'message' => 'Договор не найден',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = (new AdditionalServicesHandler())->getByOrderId($orderId, $serviceType);

            $status = $result['success'] ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

            return response()->json($result, $status);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUserAdditionalServices(Request $request): Response
    {
        $userId = (int)($request->input('user_id') ?? $request->json('user_id'));
        $orderId = (int)($request->input('order_id') ?? $request->json('order_id'));
        $contractNumber = $request->input('contract_number') ?? $request->json('contract_number');

        // Получение orderId через contract_number
        if (empty($orderId) && !empty($contractNumber)) {
            $contractNumber = formatReferenceNumber($contractNumber);
            $order = (new Order())->get(
                ["[>]s_contracts" => ["id" => "order_id"]],
                ["s_orders.id"],
                ["s_contracts.number" => $contractNumber]
            )->getData()['id'] ?? null;

            if (empty($order)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден'
                ], Response::HTTP_BAD_REQUEST);
            }

            $orderId = (int)$order;
        }

        if (empty($userId) && empty($orderId)) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь или заказ не найдены'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $handler = new AdditionalServicesHandler();
            $result = !empty($orderId)
                ? $handler->getByOrderId($orderId, null, false)
                : $handler->getByUserId($userId, false);

            $status = $result['success'] ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

            return response()->json($result, $status);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function syncStatus1C(Request $request): Response
    {
        $orderId = (int)$request->getParam('id');

        if (empty($orderId)) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан ID заявки'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->syncService->syncOrderStatus($orderId);

        $statusCode = $result->success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return response()->json($result->toArray(), $statusCode);
    }

    /**
     * POST app/orders/:id/disable-robot-calls
     * Тело (опционально): { "days": 30, "manager_id": 1 }
     */
    public function disableRobotCalls(Request $request): Response
    {
        $orderId = (int)$request->getParam('id');
        if ($orderId <= 0) {
            return Response::json([
                'success' => false,
                'message' => 'Неверный ID заявки',
            ], Response::HTTP_BAD_REQUEST);
        }

        $days = $request->input('days') ?? $request->json('days');
        $days = $days !== null ? (int)$days : null;
        $managerId = $request->input('manager_id') ?? $request->json('manager_id');
        $managerId = $managerId !== null && $managerId !== '' ? (int)$managerId : null;

        $result = $this->robotCallsHandler->disable($orderId, $days, $managerId);

        $status = $result['status'] ?? Response::HTTP_OK;
        unset($result['status']);
        return Response::json($result, $status);
    }

    /**
     * POST app/orders/:id/enable-robot-calls
     */
    public function enableRobotCalls(Request $request): Response
    {
        $orderId = (int)$request->getParam('id');
        if ($orderId <= 0) {
            return Response::json([
                'success' => false,
                'message' => 'Неверный ID заявки',
            ], Response::HTTP_BAD_REQUEST);
        }

        $managerId = $request->input('manager_id') ?? $request->json('manager_id');
        $managerId = $managerId !== null && $managerId !== '' ? (int)$managerId : null;

        $result = $this->robotCallsHandler->enable($orderId, $managerId);

        $status = $result['status'] ?? Response::HTTP_OK;
        unset($result['status']);
        return Response::json($result, $status);
    }
}
