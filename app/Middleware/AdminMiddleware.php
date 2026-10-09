<?php
/**
 * OpenBlog - 后台鉴权中间件
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

class AdminMiddleware
{
    public function handle(Request $request): bool
    {
        if (Auth::guest()) {
            Session::flash('error', '请先登录后台');
            header('Location: ' . url('admin/login'));
            return false;
        }

        return true;
    }
}
