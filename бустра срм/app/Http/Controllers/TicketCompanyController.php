<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Service\TicketCompanyService;
use App\Dto\TicketCompanyDto;
use Exception;

class TicketCompanyController
{
    private $companyService;

    public function __construct()
    {
        $this->companyService = new TicketCompanyService();
    }

    public function index(): Response
    {
        try {
            $companies = $this->companyService->getAll();
            return Response::json([
                'success' => true,
                'data' => $companies
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(Request $request): Response
    {
        try {
            $dto = TicketCompanyDto::fromRequest($request->input());
            $id = $this->companyService->create($dto);

            return Response::json([
                'success' => true,
                'message' => 'Компания успешно создана',
                'id' => $id
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request): Response
    {
        try {
            $id = (int)$request->getParam('id');
            if (!$id) {
                throw new Exception('ID компании не указан');
            }

            $dto = TicketCompanyDto::fromRequest($request->input());
            $result = $this->companyService->update($id, $dto);

            return Response::json([
                'success' => $result,
                'message' => $result ? 'Компания успешно обновлена' : 'Не удалось обновить компанию'
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request): Response
    {
        try {
            $id = (int)$request->getParam('id');
            if (!$id) {
                throw new Exception('ID компании не указан');
            }

            $result = $this->companyService->delete($id);

            return Response::json([
                'success' => in_array($result, ['deleted','deactivated']),
                'status'  => $result,
                'message' => $result === 'deleted'
                    ? 'Компания удалена'
                    : ($result === 'deactivated'
                        ? 'Компания деактивирована (используется в тикетах)'
                        : 'Не удалось удалить')
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function setUseInTickets(Request $request): Response
    {
        try {
            $id = (int)$request->getParam('id');
            if (!$id) {
                throw new Exception('ID компании не указан');
            }

            $data = $request->input();

            if (!isset($data['use_in_tickets'])) {
                throw new Exception('Статус активности не передан');
            }

            $isActive = (bool)$data['use_in_tickets'];

            $result = $this->companyService->setUseInTickets($id, $isActive);

            return Response::json([
                'success' => $result,
                'message' => $result ? 'Активность обновлена' : 'Не удалось обновить активность'
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}