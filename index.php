<?php
/**
 * OpenBlog - 前台入口
 *
 * 零框架依赖的纯 PHP 博客系统。所有请求经由此文件分发。
 */

declare(strict_types=1);

define('OPENBLOG_START', microtime(true));
define('BASE_PATH', __DIR__);

require BASE_PATH . '/app/Core/helpers.php';

/* ------------------------------------------------------------------
 * 静态资源直通：/assets/xxx  →  public/xxx
 * 使得无需额外 rewrite 规则即可访问 public 目录下的资源。
 * ------------------------------------------------------------------ */
$requestUri = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$scriptDir  = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'))), '/');

if ($scriptDir !== '' && $scriptDir !== '.' && str_starts_with($requestUri, $scriptDir)) {
    $relative = substr($requestUri, strlen($scriptDir));
} else {
    $relative = $requestUri;
}

if (str_starts_with($relative, '/assets/')) {
    $target     = BASE_PATH . '/public/' . substr($relative, strlen('/assets/'));
    $real       = realpath($target);
    $publicRoot = realpath(BASE_PATH . '/public');

    if ($real !== false && $publicRoot !== false && is_file($real) && str_starts_with($real, $publicRoot)) {
        $mime = match (strtolower(pathinfo($real, PATHINFO_EXTENSION))) {
            'css'  => 'text/css; charset=utf-8',
            'js'   => 'application/javascript; charset=utf-8',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ico'  => 'image/x-icon',
            default=> 'application/octet-stream',
        };

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=604800');
        header('X-Content-Type-Options: nosniff');
        readfile($real);
        exit;
    }

    http_response_code(404);
    exit;
}

/* ------------------------------------------------------------------
 * 安装检查
 * ------------------------------------------------------------------ */
$config = require BASE_PATH . '/app/config.php';

if (!($config['installed'] ?? false)) {
    header('Location: ' . ($scriptDir === '' ? '' : $scriptDir) . '/install/');
    exit;
}

require BASE_PATH . '/app/bootstrap.php';
