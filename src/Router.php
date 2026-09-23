<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler, ?string $rol = null): void
    {
        $this->routes['GET'][$path] = [$handler, $rol];
    }

    public function post(string $path, callable $handler, ?string $rol = null): void
    {
        $this->routes['POST'][$path] = [$handler, $rol];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $route => [$handler, $rol]) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\d+)', $route);
            if (preg_match("#^{$pattern}$#", $path, $params)) {
                if ($rol) {
                    Auth::require($rol);
                }
                $params = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);
                ($handler)($params);
                return;
            }
        }

        http_response_code(404);
        echo "404 - No encontrado";
    }
}