<?php

namespace App\Http\Middleware;

use App\Core\Application\Application;
use App\Core\Application\Request\Request;
use App\Core\Application\Session\Session;
use App\Core\Application\Response\Response;
use App\Repositories\ApplicationTokenRepository;
use App\Repositories\ManagerRepository;

/**
 * Проверяет доступ: либо Bearer-токен приложения (application_tokens), либо сессия менеджера.
 * Позволяет вызывать маршрут и из CRM, и извне по API с токеном.
 */
class CheckManagerOrToken implements MiddlewareInterface
{
    public function handle(Request $request, array $guards): bool
    {
        $token = $request->bearerToken();
        if ($token !== null && $token !== '') {
            $tokenRepository = Application::getInstance()->make(ApplicationTokenRepository::class);
            if ($tokenRepository->isValidToken($token)) {
                return true;
            }
        }

        $session = Session::getInstance();
        if ($session->has('manager_id') && $session->get('manager_id')) {
            $managerId = (int) $session->get('manager_id');
            $managerRepository = Application::getInstance()->make(ManagerRepository::class);
            $manager = $managerRepository->getById($managerId);
            if ($manager !== null) {
                return true;
            }
        }

        return $this->unauthorized();
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
