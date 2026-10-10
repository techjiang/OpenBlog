<?php
/**
 * 本地开发用 PHP 内置服务器 router：
 *  - 注册 PSR-4 自动加载（零依赖运行，无需 composer install）
 *  - public/ 下的静态资源直接输出（文档根在仓库根，需手动映射到 public 目录）
 *  - 其余请求交给仓库根 index.php 处理
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
$file = __DIR__ . '/public' . $uri;

$staticExt = [
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'mjs'  => 'application/javascript',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'webp' => 'image/webp',
    'ico'  => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'  => 'font/ttf',
    'json' => 'application/json',
    'map'  => 'application/json',
];

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if ($uri !== '/' && is_file($file) && !is_dir($file) && isset($staticExt[$ext])) {
    header('Content-Type: ' . $staticExt[$ext]);
    header('Content-Length: ' . filesize($file));
    readfile($file);
    return true;
}

require __DIR__ . '/index.php';
