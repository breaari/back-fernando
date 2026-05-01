<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $currentMethod;
    private $currentPath;

    public function __construct()
    {
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
        $this->currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->currentPath = str_replace('/back-php/public', '', $this->currentPath);
    }

    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function patch($path, $handler)
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    private function addRoute($method, $path, $handler)
    {
        // Extraer nombres de parámetros en orden
        $paramNames = [];
        preg_replace_callback('/{([a-zA-Z0-9_]+)}/', function ($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return $matches[0];
        }, $path);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->pathToRegex($path),
            'paramNames' => $paramNames,
        ];
    }

    private function pathToRegex($path)
    {
        $pattern = preg_replace_callback('/{([a-zA-Z0-9_]+)}/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $path);

        return '^' . $pattern . '$';
    }

    public function dispatch()
    {
        foreach ($this->routes as $route) {
            if (
                $route['method'] === $this->currentMethod &&
                preg_match('#' . $route['pattern'] . '#', $this->currentPath, $matches)
            ) {
                // Extraer parámetros en el orden correcto
                $params = [];
                foreach ($route['paramNames'] as $paramName) {
                    $params[] = $matches[$paramName] ?? null;
                }
                return $this->callHandler($route['handler'], $params);
            }
        }

        Response::notFound('Route not found');
    }

    private function callHandler($handler, $params)
    {
        if (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);
            $controllerClass = 'App\\Controllers\\' . $controller;

            if (!class_exists($controllerClass)) {
                Response::error('Controller not found', 500);
            }

            $instance = new $controllerClass();
            return $instance->$method(...$params);
        } elseif (is_callable($handler)) {
            return $handler(...$params);
        }

        Response::error('Invalid handler', 500);
    }
}
