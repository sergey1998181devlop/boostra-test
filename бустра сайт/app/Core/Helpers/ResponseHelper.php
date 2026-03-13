<?php


use App\Core\Application\Redirect\Redirect;
use App\Core\Application\Response\Response;

if (!function_exists('redirect')) {
    function redirect(string $redirectLink): Redirect {
        return (new Redirect())->redirect($redirectLink);
    }
}

if (!function_exists('back')) {
    function back(): Redirect {
        return (new Redirect())->back();
    }
}

if (!function_exists('response')) {
    function response(): Response {
        return (new Response());
    }
}
