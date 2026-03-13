<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Service\CbRequestService;
use Exception;

class CbRequestController
{
    /** @var CbRequestService */
    private CbRequestService $service;

    public function __construct(CbRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * POST app/cb-requests
     *
     * Создать или обновить запрос ЦБ из парсера.
     */
    public function store(Request $request): Response
    {
        try {
            $data = $request->input();
            if (empty($data)) {
                $data = $request->json();
            }

            if (empty($data)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Пустое тело запроса',
                ], Response::HTTP_BAD_REQUEST);
            }

            $result = $this->service->createOrUpdateFromParser($data);

            return Response::json([
                'success' => true,
                'id' => $result['id'],
                'files_count' => $result['files_count'],
                'updated' => $result['updated'],
            ]);
        } catch (Exception $e) {
            $code = strpos($e->getMessage(), 'Обязательное поле') !== false
                ? Response::HTTP_UNPROCESSABLE_ENTITY
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }
}
