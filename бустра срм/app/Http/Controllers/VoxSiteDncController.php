<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Service\VoxSiteDncService;
use Exception;

class VoxSiteDncController
{
    /** @var VoxSiteDncService */
    private VoxSiteDncService $service;

    public function __construct(VoxSiteDncService $service)
    {
        $this->service = $service;
    }

    /**
     * GET app/vox-site-dnc?site_id=...
     */
    public function index(Request $request): Response
    {
        try {
            $siteId = $request->query('site_id');
            $siteId = $siteId !== null && $siteId !== '' ? trim((string)$siteId) : null;
            $list = $this->service->getList($siteId);
            return Response::json([
                'success' => true,
                'data' => $list,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET app/vox-site-dnc/:id
     */
    public function show(Request $request): Response
    {
        try {
            $id = (int)$request->getParam('id');
            if (!$id) {
                throw new Exception('ID не указан');
            }
            $item = $this->service->getById($id);
            if ($item === null) {
                return Response::json([
                    'success' => false,
                    'message' => 'Запись не найдена',
                ], Response::HTTP_NOT_FOUND);
            }
            return Response::json([
                'success' => true,
                'data' => $item,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST app/vox-site-dnc
     */
    public function store(Request $request): Response
    {
        try {
            $data = $request->input();
            if (empty($data)) {
                $data = $request->json();
            }
            $id = $this->service->create($data);
            $item = $this->service->getById($id);
            return Response::json([
                'success' => true,
                'message' => 'Запись создана',
                'id' => $id,
                'data' => $item,
            ]);
        } catch (Exception $e) {
            $code = strpos($e->getMessage(), 'уже существует') !== false
                ? Response::HTTP_CONFLICT
                : Response::HTTP_UNPROCESSABLE_ENTITY;
            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    /**
     * PUT app/vox-site-dnc/:id
     */
    public function update(Request $request): Response
    {
        try {
            $id = (int)$request->getParam('id');
            if (!$id) {
                throw new Exception('ID не указан');
            }
            $data = $request->input();
            if (empty($data)) {
                $data = $request->json();
            }
            $this->service->update($id, $data);
            $item = $this->service->getById($id);
            return Response::json([
                'success' => true,
                'message' => 'Запись обновлена',
                'data' => $item,
            ]);
        } catch (Exception $e) {
            $code = $e->getMessage() === 'Запись не найдена'
                ? Response::HTTP_NOT_FOUND
                : (strpos($e->getMessage(), 'уже существует') !== false
                    ? Response::HTTP_CONFLICT
                    : Response::HTTP_UNPROCESSABLE_ENTITY);
            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    /**
     * DELETE app/vox-site-dnc/:id
     */
    public function destroy(Request $request): Response
    {
        try {
            $id = (int)$request->getParam('id');
            if (!$id) {
                throw new Exception('ID не указан');
            }
            $this->service->delete($id);
            return Response::json([
                'success' => true,
                'message' => 'Запись удалена',
            ]);
        } catch (Exception $e) {
            $code = $e->getMessage() === 'Запись не найдена'
                ? Response::HTTP_NOT_FOUND
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }
}
