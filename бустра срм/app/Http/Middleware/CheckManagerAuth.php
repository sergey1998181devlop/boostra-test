<?php

namespace App\Http\Middleware;

use App\Core\Application\Application;
use App\Core\Application\Request\Request;
use App\Core\Application\Session\Session;
use App\Core\Application\Response\Response;
use App\Repositories\ManagerRepository;

/**
 * Проверяет, что запрос от авторизованного менеджера.
 * Проверка менеджера в БД через ManagerRepository.
 */
class CheckManagerAuth implements MiddlewareInterface
{
    public function handle(Request $request, array $guards): bool
    {
        $session = Session::getInstance();
        if (!$session->has('manager_id') || !$session->get('manager_id')) {
            return $this->unauthorized();
        }

        $managerId = (int)$session->get('manager_id');
        $managerRepository = Application::getInstance()->make(ManagerRepository::class);
        $manager = $managerRepository->getById($managerId);
        if ($manager === null) {
            return $this->unauthorized();
        }

        return true;
    }

    private function unauthorized(): bool
    {
        response()->json([
            'message' => 'Unauthorized',
            'success' => false,
        ], Response::HTTP_UNAUTHORIZED)->send();
        return false;
    }
}
