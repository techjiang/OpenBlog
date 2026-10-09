<?php
/**
 * 本地开发用 PHP 内置服务器 router：
 * 注册 PSR-4 自动加载（零依赖运行），真实文件直接返回，其余请求交给 index.php。
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = __DIR__ . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file) && !is_dir($file)) {
    return false;
}
require __DIR__ . '/index.php';
