<?php

namespace App\Http\Middleware;


use App\Core\Application\Request\Request;

interface MiddlewareInterface {
    public function handle(Request $request, array $guards): bool;
}
