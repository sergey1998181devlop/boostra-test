<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Handlers\UserDncHandler;

class UserDncController
{
    /**
     * Создание DNC-записи
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $phone = $request->input('phone') ?? $request->json('phone');
        $days = (int)$request->input('days', 1) ?? (int)$request->json('days', 1);
        $managerId = (int)$request->input('manager_id') ?? (int)$request->json('manager_id');

        $result = (new UserDncHandler())->handle($phone, $days, $managerId);

        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * Создание DNC-записи до даты платежа
     *
     * @param Request $request
     * @return Response
     */
    public function createByPaymentDate(Request $request): Response
    {
        $phone = $request->input('phone') ?? $request->json('phone');
        $managerId = (int)$request->input('manager_id') ?? (int)$request->json('manager_id');

        $result = (new UserDncHandler())->handleByPaymentDate($phone, $managerId);

        return response()->json($result, $result['status'] ?? 200);
    }
}