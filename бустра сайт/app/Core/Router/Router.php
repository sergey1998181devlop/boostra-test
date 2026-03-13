<?php

namespace App\Core\Router;

use App\Core\Application\Request\Request;
use App\Core\Application\Traits\Singleton;
use Closure;
use App\Core\Middleware\BaseMiddleware;
use Exception;

class Router {
    use Singleton;

    protected array $routes = [];
    protected string $path;
    protected $method;
    public Request $request;

    public function __construct() {
        $this->path = $this->normalizePath($_SERVER['REQUEST_URI']);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    private function normalizePath($path): string
    {
        $path = explode('?', $path)[0];
        return rtrim('/' . trim($path, '/'), '/') ?: '/';
    }

    private function addRoute($method, $pattern, $callback, array $middleware = [])
    {
        $pattern = $this->normalizePath($pattern);
        $this->routes[$method][$pattern] = [
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    /**
     * @throws Exception
     */
    public function dispatch()
    {
        foreach ($this->routes[$this->method] ?? [] as $pattern => $route) {
            if ($this->matchPattern($pattern, $this->path)) {
                $params = $this->extractParams($pattern, $this->path);

                $this->request->setParams($params);

                BaseMiddleware::resolve($route['middleware'], $this->request);

                if ($route['callback'] instanceof Closure) {
                    return call_user_func_array($route['callback'], [$params]);
                } elseif (is_array($route['callback'])) {
                    if (is_string($route['callback'][0]) && is_string($route['callback'][1])) {
                        $controllerInstance = app()->make($route['callback'][0]);

                        return call_user_func_array(
                            [$controllerInstance, $route['callback'][1]],
                            [$this->request]
                        );
                    }
                }
            }
        }

        throw new Exception("Route not found", 404);
    }

    private function matchPattern($pattern, $path)
    {
        // Convert custom route patterns to regex
        $pattern = preg_replace_callback('/:(\w+)\??/', function($matches) {
            // Check if the parameter is optional
            $optional = substr($matches[0], -1) === '?';
            return $optional ? '(?<' . $matches[1] . '>[^/]*)' : '(?<' . $matches[1] . '>[^/]+)';
        }, $pattern);

        $pattern = '@^' . $pattern . '$@';
        return preg_match($pattern, $path);
    }

    private function extractParams($pattern, $path): array
    {
        $params = [];

        // Convert custom route patterns to regex
        $pattern = preg_replace_callback('/:(\w+)\??/', function($matches) {
            $optional = substr($matches[0], -1) === '?';
            return $optional ? '(?<' . $matches[1] . '>[^/]*)' : '(?<' . $matches[1] . '>[^/]+)';
        }, $pattern);

        if (preg_match('@^' . $pattern . '$@', $path, $matches)) {
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    // Sanitize parameter values to remove unsafe characters
                    $params[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                }
            }
        }

        return $params;
    }

    public function get($pattern, $callback, array $middleware = [])
    {
        $this->addRoute('GET', $pattern, $callback, $middleware);
    }

    public function post($pattern, $callback, array $middleware = [])
    {
        $this->addRoute('POST', $pattern, $callback, $middleware);
    }

    public function put($pattern, $callback, array $middleware = [])
    {
        $this->addRoute('PUT', $pattern, $callback, $middleware);
    }

    public function patch($pattern, $callback, array $middleware = [])
    {
        $this->addRoute('PATCH', $pattern, $callback, $middleware);
    }

    public function delete($pattern, $callback, array $middleware = [])
    {
        $this->addRoute('DELETE', $pattern, $callback, $middleware);
    }

    public function setDependency(Request $request)
    {
        $this->request = $request;
    }
}