<?php
/**
 * OpenBlog - 本地开发 / 演示配置（SQLite 版）
 *
 * index.php 与 tools/seed_sqlite.php 都会优先加载本文件（若存在于 app/ 下）。
 * 因此克隆仓库后无需修改 config.php 即可一键运行演示数据。
 *
 * 生产部署：删除或忽略本文件，使用 config.php（MySQL）并在 Web 服务器中指向入口。
 */

return [
    'app' => [
        'name'      => 'OpenBlog',
        'url'       => '',
        'debug'     => true,
        'timezone'  => 'Asia/Shanghai',
        'locale'    => 'zh-CN',
    ],

    'db' => [
        'driver' => 'sqlite',
        'path'   => __DIR__ . '/../storage/openblog.sqlite',
    ],

    'installed' => true,
];
