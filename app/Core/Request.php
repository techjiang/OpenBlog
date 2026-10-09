<?php
/**
 * OpenBlog - HTTP 请求封装
 */

declare(strict_types=1);

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private string $path;
    /** @var array<string, mixed> */
    private array $query;
    /** @var array<string, mixed> */
    private array $post;
    /** @var array<string, mixed> */
    private array $server;

    private function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->server = $_SERVER;

        $rawMethod = $_POST['_method'] ?? null;
        if ($this->method === 'POST' && is_string($rawMethod)) {
            $this->method = strtoupper($rawMethod);
        }

        $this->uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $parsed = parse_url($this->uri, PHP_URL_PATH) ?: '/';
        $this->path = $this->normalizePath($parsed);

        $this->query = $_GET;
        $this->post = $_POST;
    }

    public static function capture(): self
    {
        return new self();
    }

    private function normalizePath(string $path): string
    {
        $scriptDir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
        $scriptDir = rtrim($scriptDir, '/');

        if ($scriptDir !== '' && $scriptDir !== '.' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
        }

        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isAjax(): bool
    {
        return strtolower((string)($this->server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    public function path(): string
    {
        return $this->path;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->post);
    }

    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }
        return $result;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }

    public function int(string $key, int $default = 0): int
    {
        return (int)$this->input($key, $default);
    }

    public function bool(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '' && $value !== '0' && $value !== false;
    }

    public function ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($this->server[$key])) {
                $value = (string)$this->server[$key];
                return trim(explode(',', $value)[0]);
            }
        }
        return '0.0.0.0';
    }

    public function userAgent(): string
    {
        return substr((string)($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function referer(): string
    {
        return (string)($this->server['HTTP_REFERER'] ?? '');
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}
