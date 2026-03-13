<?php

namespace App\Http\Controllers;

use App\Core\Application\Application;
use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Dto\SendLicenseSmsDto;
use App\Dto\SendSmsDto;
use App\Service\LicenseSmsService;
use App\Service\SmsService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Контроллер для отправки SMS
 */
class SmsController
{
    private SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Отправка SMS
     *
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     */
    public function send(Request $request)
    {
        $dto = SendSmsDto::fromRequest($request->input());
        $managerId = (int)($request->input('manager_id') ?? null);
        
        $result = $this->smsService->sendSms($dto, $managerId);
        $status = $result['status'] ?? Response::HTTP_OK;
        unset($result['status']);
        return response()->json($result, $status);
    }

    /**
     * Отправка SMS с лицензионным ключом
     *
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     */
    public function sendLicenseSms(Request $request): Response
    {
        try {
            $dto = SendLicenseSmsDto::fromRequest($request->input());

            $errors = $dto->validate();
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => implode(', ', $errors)
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $app = Application::getInstance();
            /** @var LicenseSmsService $licenseSmsService */
            $licenseSmsService = $app->make(LicenseSmsService::class);
            $result = $licenseSmsService->sendLicenseSms($dto);

            $status = $result['success'] ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

            return response()->json($result, $status);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}