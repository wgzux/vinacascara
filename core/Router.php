<?php
namespace Core;

class Router {
    private array $routes = [];

    public function get($uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute($method, $uri, $action) {
        $this->routes[] = [
            'method' => $method,
            'uri'    => $uri,
            'action' => $action
        ];
    }

    public function dispatch($uri, $method) {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            // Convert route vars like {id} to regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['uri']);
            $pattern = "#^$pattern$#";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                if (is_callable($route['action'])) {
                    return call_user_func_array($route['action'], $matches);
                }

                if (is_array($route['action'])) {
                    $controller = new $route['action'][0]();
                    $methodName = $route['action'][1];
                    return call_user_func_array([$controller, $methodName], $matches);
                }
            }
        }

        // 404
        http_response_code(404);
        echo "404 Not Found";
    }
}
