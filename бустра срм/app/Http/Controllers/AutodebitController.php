<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Dto\ToggleAutodebitDto;
use App\Enums\LogAction;
use App\Handlers\ToggleCardAutodebitHandler;
use App\Handlers\ToggleSbpAutodebitHandler;
use App\Repositories\OrdersRepository;
use App\Service\ChangeLogs\ActionLoggerService;

class AutodebitController
{
    private OrdersRepository $orderRepository;
    private ToggleCardAutodebitHandler $cardHandler;
    private ToggleSbpAutodebitHandler $sbpHandler;
    private ActionLoggerService $actionLogger;

    public function __construct(
        OrdersRepository $orderRepository,
        ToggleCardAutodebitHandler $cardHandler,
        ToggleSbpAutodebitHandler $sbpHandler,
        ActionLoggerService $actionLogger
    ) {
        $this->orderRepository = $orderRepository;
        $this->cardHandler = $cardHandler;
        $this->sbpHandler = $sbpHandler;
        $this->actionLogger = $actionLogger;
    }
    /**
     * Массово включает/выключает автодебет для всех карт и СБП-счетов пользователя по order_id.
     *
     * Параметры:
     * - value: 0|1 (обязателен)
     * - manager_id: int (опционально, по умолчанию системный 50)
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function toggleAutodebitForUser(Request $request): Response
    {
        $validationResult = $this->validateRequest($request);
        
        if ($validationResult['response'] !== null) {
            return $validationResult['response'];
        }

        $dto = $validationResult['dto'];

        // Обработка карт
        $cardResult = $this->cardHandler->handle(
            $dto->userId,
            $dto->orderId,
            $dto->value,
            $dto->managerId
        );

        // Обработка СБП-счетов
        $sbpResult = $this->sbpHandler->handle(
            $dto->userId,
            $dto->orderId,
            $dto->value,
            $dto->managerId
        );

        $hasOperationErrors = !$cardResult['success'] || !$sbpResult['success'];
        
        if ($hasOperationErrors) {
            $messages = array_filter([
                !$cardResult['success'] ? $cardResult['message'] : null,
                !$sbpResult['success'] ? $sbpResult['message'] : null,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => implode(', ', $messages),
                'cards' => $cardResult,
                'sbp' => $sbpResult
            ], Response::HTTP_NOT_FOUND);
        }

        // Логирование операции
        $this->logAutodebitOperation(
            $dto,
            LogAction::TOGGLE_AUTODEBIT_ON_ALL,
            LogAction::TOGGLE_AUTODEBIT_OFF_ALL
        );

        return response()->json([
            'success' => true,
            'cards' => $cardResult,
            'sbp' => $sbpResult
        ]);
    }

    /**
     * Устанавливает статус автосписаний по всем картам клиента (рекуррентные платежи).
     *
     * Определяет клиента по ID заявки (order_id) и массово применяет значение
     * автосписания ко всем его активным картам.
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function toggleAutodebitForUserCards(Request $request): Response
    {
        $validationResult = $this->validateRequest($request);
        
        if ($validationResult['response'] !== null) {
            return $validationResult['response'];
        }

        $dto = $validationResult['dto'];

        $result = $this->cardHandler->handle(
            $dto->userId,
            $dto->orderId,
            $dto->value,
            $dto->managerId
        );

        $errorResponse = $this->handleErrorResponse($result);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $this->logAutodebitOperation(
            $dto,
            LogAction::TOGGLE_AUTODEBIT_ON_CARDS,
            LogAction::TOGGLE_AUTODEBIT_OFF_CARDS
        );

        return response()->json($result);
    }

    /**
     * Массово включает/выключает автодебет для всех СБП-счетов пользователя по order_id.
     *
     * Параметры:
     * - value: 0|1 (обязателен)
     * - manager_id: int (опционально, по умолчанию системный 50)
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function toggleAutodebitForUserSbpAccounts(Request $request): Response
    {
        $validationResult = $this->validateRequest($request);
        
        if ($validationResult['response'] !== null) {
            return $validationResult['response'];
        }

        $dto = $validationResult['dto'];

        $result = $this->sbpHandler->handle(
            $dto->userId,
            $dto->orderId,
            $dto->value,
            $dto->managerId
        );

        $errorResponse = $this->handleErrorResponse($result);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $this->logAutodebitOperation(
            $dto,
            LogAction::TOGGLE_AUTODEBIT_ON_SBP,
            LogAction::TOGGLE_AUTODEBIT_OFF_SBP
        );

        return response()->json($result);
    }

    /**
     * Валидирует запрос и возвращает DTO или Response с ошибкой
     * @param Request $request
     * @return array Массив с ключами 'dto' (ToggleAutodebitDto|null) и 'response' (Response|null)
     */
    private function validateRequest(Request $request): array
    {
        $dto = ToggleAutodebitDto::fromRequest($request);

        $validationErrors = $dto->validate();
        
        if (!empty($validationErrors)) {
            $status = Response::HTTP_BAD_REQUEST;
            if (count($validationErrors) === 1 && strpos($validationErrors[0], 'Некорректное значение параметра value') !== false) {
                $status = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
            
            return [
                'dto' => null,
                'response' => response()->json([
                    'success' => false,
                    'message' => implode(', ', $validationErrors)
                ], $status)
            ];
        }

        $userId = $this->orderRepository->findByIdWithUserId($dto->orderId);
        
        if ($userId === null) {
            return [
                'dto' => null,
                'response' => response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден'
                ], Response::HTTP_NOT_FOUND)
            ];
        }

        $dto->userId = $userId;

        return [
            'dto' => $dto,
            'response' => null
        ];
    }

    /**
     * Обрабатывает ошибку результата операции и возвращает Response с ошибкой или null
     * @param array $result
     * @return Response|null
     */
    private function handleErrorResponse(array $result): ?Response
    {
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], Response::HTTP_NOT_FOUND);
        }

        return null;
    }

    /**
     * Логирует операцию автодебета
     * @param ToggleAutodebitDto $dto
     * @param string $onAction Константа LogAction для включения
     * @param string $offAction Константа LogAction для выключения
     * @return void
     */
    private function logAutodebitOperation(
        ToggleAutodebitDto $dto,
        string $onAction,
        string $offAction
    ): void {
        $action = $dto->value === ToggleAutodebitDto::AUTODEBIT_ENABLED
            ? new LogAction($onAction)
            : new LogAction($offAction);
        
        $this->actionLogger->log($action, $dto->userId, [
            'manager_id' => $dto->managerId,
            'order_id' => $dto->orderId,
        ]);
    }

}
