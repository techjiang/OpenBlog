<?php
/**
 * OpenBlog - 路由
 *
 * 支持 GET/POST 等谓词、{id} 与 {slug} 占位符、分组前缀。
 */

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<int, array{0:string,1:string,2:mixed}> */
    private array $routes = [];

    private string $groupPrefix = '';
    private string $groupNamespace = 'App\\Controllers\\';
    /** @var array<int, string> */
    private array $groupMiddleware = [];

    public function setNamespace(string $namespace): self
    {
        $this->groupNamespace = rtrim($namespace, '\\') . '\\';
        return $this;
    }

    public function get(string $uri, mixed $action): self
    {
        return $this->add('GET', $uri, $action);
    }

    public function post(string $uri, mixed $action): self
    {
        return $this->add('POST', $uri, $action);
    }

    public function put(string $uri, mixed $action): self
    {
        return $this->add('PUT', $uri, $action);
    }

    public function delete(string $uri, mixed $action): self
    {
        return $this->add('DELETE', $uri, $action);
    }

    public function match(array $methods, string $uri, mixed $action): self
    {
        foreach ($methods as $method) {
            $this->add(strtoupper($method), $uri, $action);
        }
        return $this;
    }

    public function group(string $prefix, callable $callback, string $namespace = '', array $middleware = []): void
    {
        $previous = [$this->groupPrefix, $this->groupNamespace, $this->groupMiddleware];

        $this->groupPrefix = rtrim($previous[0] . '/' . trim($prefix, '/'), '/');
        $this->groupNamespace = $namespace !== ''
            ? rtrim($previous[1] . $namespace, '\\') . '\\'
            : $previous[1];
        $this->groupMiddleware = array_merge($previous[2], $middleware);

        $callback($this);

        [$this->groupPrefix, $this->groupNamespace, $this->groupMiddleware] = $previous;
    }

    private function add(string $method, string $uri, mixed $action): self
    {
        $uri = $this->groupPrefix . '/' . trim($uri, '/');
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $this->routes[] = [
            $method,
            $uri,
            $action,
            $this->groupNamespace,
            $this->groupMiddleware,
        ];

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes as [$routeMethod, $routeUri, $action, $namespace, $middleware]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $params = $this->matchUri($routeUri, $path);
            if ($params === null) {
                continue;
            }

            foreach ($middleware as $mw) {
                $instance = new $mw();
                if (!$instance->handle($request)) {
                    return;
                }
            }

            $this->callAction($action, $params, $namespace);
            return;
        }

        $this->abort404();
    }

    private function matchUri(string $routeUri, string $path): ?array
    {
        if ($routeUri === $path) {
            return [];
        }

        if (!str_contains($routeUri, '{')) {
            return null;
        }

        $pattern = preg_replace_callback(
            '#\{(\w+)(?::([^}]+))?\}#',
            static fn (array $m): string => '(?P<' . $m[1] . '>' . ($m[2] !== '' ? $m[2] : '[^/]+') . ')',
            $routeUri
        );
        $pattern = '#^' . str_replace('#', '\#', $pattern) . '$#';

        if (!preg_match($pattern, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    private function callAction(mixed $action, array $params, string $namespace): void
    {
        if ($action instanceof \Closure) {
            echo $this->invoke($action, $params);
            return;
        }

        if (is_string($action)) {
            $class = $namespace . $action;
            if (!class_exists($class)) {
                throw new \RuntimeException("控制器不存在：{$class}");
            }
            $controller = new $class();
            echo $this->invoke([$controller, 'handle'], $params);
            return;
        }

        if (is_array($action) && count($action) === 2) {
            [$controllerName, $method] = $action;
            $class = $namespace . $controllerName;
            $controller = new $class();
            echo $this->invoke([$controller, $method], $params);
            return;
        }

        throw new \RuntimeException('无法解析的路由动作');
    }

    private function invoke(callable $callable, array $params): mixed
    {
        $reflection = is_array($callable)
            ? new \ReflectionMethod($callable[0], $callable[1])
            : new \ReflectionFunction($callable);

        $args = [];
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
            } elseif ($parameter->getType()?->getName() === Request::class) {
                $args[] = App::instance()->request;
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        return $callable(...$args);
    }

    private function abort404(): void
    {
        http_response_code(404);
        $view = new View();
        echo $view->render('errors/404', [
            'title'   => '页面不存在',
            'message' => '你访问的地址可能已被移动或删除。',
        ], 'main');
        exit;
    }
}
