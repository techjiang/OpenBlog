<?php
/**
 * OpenBlog - 应用引导程序
 * 
 * 负责加载配置、初始化会话、注册自动加载、启动应用。
 */

declare(strict_types=1);

namespace App\Core;

final class App
{
    private static ?App $instance = null;

    /** @var array<string, mixed> */
    public array $config = [];

    public Database $db;
    public Router $router;
    public Request $request;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->bootstrap();
    }

    private function bootstrap(): void
    {
        $this->loadConfig();
        $this->initErrorHandling();
        $this->registerAutoload();
        $this->initSession();

        $this->request = Request::capture();

        $this->db = new Database($this->config['db']);

        Settings::boot($this->db);

        date_default_timezone_set($this->config['app']['timezone'] ?? 'Asia/Shanghai');

        $this->router = new Router();
        $this->registerRoutes();
    }

    private function loadConfig(): void
    {
        $configFile = BASE_PATH . '/app/config.php';
        if (!is_file($configFile)) {
            throw new \RuntimeException('缺少配置文件 app/config.php');
        }
        $this->config = require $configFile;
    }

    private function initErrorHandling(): void
    {
        $debug = (bool)($this->config['app']['debug'] ?? false);

        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        ini_set('display_errors', $debug ? '1' : '0');

        set_exception_handler(static function (\Throwable $e) use ($debug): void {
            http_response_code(500);
            if ($debug) {
                echo '<pre style="padding:24px;font:14px/1.6 monospace">';
                echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n\n";
                echo htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
                echo '</pre>';
            } else {
                echo '<!doctype html><meta charset="utf-8"><title>500</title>';
                echo '<div style="font-family:system-ui;padding:80px;text-align:center">';
                echo '<h1 style="font-size:72px;margin:0">500</h1><p>服务器开小差了，请稍后再试。</p></div>';
            }
            exit;
        });
    }

    private function registerAutoload(): void
    {
        spl_autoload_register(static function (string $class): void {
            $prefix = 'App\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $relative = substr($class, strlen($prefix));
            $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
        });
    }

    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = ($this->config['app']['url'] ?? '') !== '' && str_starts_with((string)$this->config['app']['url'], 'https');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => $secure,
            ]);
            session_name('openblog_session');
            session_start();
        }
    }

    private function registerRoutes(): void
    {
        // 注意：此处仍处于构造流程中，routes.php 内禁止调用 App::instance()
        $router = $this->router;
        require BASE_PATH . '/app/routes.php';
    }

    public function run(): void
    {
        $this->router->dispatch($this->request);
    }

    public static function conf(string $key, mixed $default = null): mixed
    {
        $app = self::instance();
        $segments = explode('.', $key);
        $value = $app->config;
        foreach ($segments as $seg) {
            if (!is_array($value) || !array_key_exists($seg, $value)) {
                return $default;
            }
            $value = $value[$seg];
        }
        return $value;
    }
}
