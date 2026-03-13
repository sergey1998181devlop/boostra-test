<?php

namespace App\Http\Middleware;

use App\Core\Application\Application;
use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Repositories\ApplicationTokenRepository;

class CheckApplicationToken implements MiddlewareInterface
{
    public function handle(Request $request, array $guards): bool
    {
        $token = $request->bearerToken();

        $tokenRepository = Application::getInstance()->make(ApplicationTokenRepository::class);
        if (empty($token) || !$tokenRepository->isValidToken($token)) {
            response()->json([
                'message' => 'Token not found'
            ], Response::HTTP_UNAUTHORIZED)->send();
            return false;
        }

        return true;
    }
}
