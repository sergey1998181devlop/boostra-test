<?php

namespace App\Core\Application\Container;

use App\Core\Application\Application;
use App\Core\Application\Container\Contracts\ContainerInterface;
use App\Core\Application\Request\Request;
use App\Core\Database\BaseDatabase;
use App\Core\Router\Router;

class ApplicationContainer extends BaseContainer implements ContainerInterface {

    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->bind('App', function () {
            return Application::singleton();
        });

        $this->app->bind('DB', function () {
            return BaseDatabase::singleton();
        });

        $this->app->bind('Request', function () {
            return Request::singleton();
        });

        $this->app->bind('Router', function () {
            $router = Router::singleton();

            $router->setDependency(Request::singleton());

            return $router;
        });
    }

    /**
     * Bootstrap any application services
     * and if you want to do something before handling the request.
     */
    public function boot(): void {
        //
    }
}