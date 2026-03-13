<?php

namespace App\Http\Middleware;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;

class CheckUsedeskUserTicketSecret implements MiddlewareInterface
{
    public function handle(Request $request, array $guards): bool
    {
        $secret = config('services.usedesk.user_ticket_app_id');

        if ($request->json('secret') !== $secret) {
            response()->json([
                'message' => 'Invalid secret key'
            ], Response::HTTP_PERMISSION_DENIED)->send();
            return false;
        }

        return true;
    }
}
