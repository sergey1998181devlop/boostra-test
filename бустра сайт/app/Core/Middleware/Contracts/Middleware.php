<?php

namespace App\Core\Middleware\Contracts;

use App\Core\Application\Request\Request;

interface Middleware {

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, array $guards) ;

}
