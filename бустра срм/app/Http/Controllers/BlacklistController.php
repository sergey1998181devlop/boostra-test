<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Service\BlacklistService;

class BlacklistController
{
    private BlacklistService $service;

    public function __construct(BlacklistService $service)
    {
        $this->service = $service;
    }

    public function check(Request $request): Response
    {
        $phone = formatPhoneNumber($request->input('phone'));

        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан номер телефона'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'blocked' => $this->service->isBlocked($phone)
        ], Response::HTTP_OK);
    }

    public function add(Request $request): Response
    {
        $phone = $request->input('phone');
        $reason = $request->input('reason');
        $managerId = (int)$request->input('manager_id');

        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан номер телефона'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$managerId) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан ID менеджера'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $id = $this->service->addToBlacklist([
                'phone_number' => $phone,
                'reason' => $reason,
                'created_by' => $managerId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Номер успешно добавлен в черный список',
                'data' => ['id' => $id]
            ], Response::HTTP_OK);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'PHONE_EXISTS'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении номера: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request): Response
    {
        $id = (int)$request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан ID записи'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->service->deleteFromBlacklist($id);

            return response()->json([
                'success' => true,
                'message' => 'Номер успешно удален из черного списка'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении номера: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function toggleStatus(Request $request): Response
    {
        $id = (int)$request->input('id');
        $status = (bool)$request->input('status');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Не указан ID записи'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->service->toggleStatus($id, $status);

            return response()->json([
                'success' => true,
                'message' => 'Статус успешно изменен'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при изменении статуса: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 