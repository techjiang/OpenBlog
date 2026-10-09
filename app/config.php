<?php
/**
 * OpenBlog - 应用配置
 *
 * 安装向导会重写此文件。也可以直接手改 db 段。
 */

declare(strict_types=1);

return [
    'app' => [
        'name'      => 'OpenBlog',
        'url'       => '',            // 留空则自动检测，例如 https://blog.example.com
        'debug'     => true,          // 生产环境请改为 false
        'timezone'  => 'Asia/Shanghai',
        'locale'    => 'zh-CN',
    ],

    'db' => [
        'host'     => '127.0.0.1',
        'port'     => '3306',
        'database' => 'openblog',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    'installed' => false,
];
