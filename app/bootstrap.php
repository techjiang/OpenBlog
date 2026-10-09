<?php
/**
 * OpenBlog - 启动文件
 */

declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;

require_once BASE_PATH . '/app/Core/helpers.php';

$app = App::instance();

Auth::resolveRemember();

$app->run();
