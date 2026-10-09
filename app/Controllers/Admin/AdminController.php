<?php
/**
 * OpenBlog - 后台控制器基类
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\App;
use App\Core\Auth;
use App\Models\Category;
use App\Models\Post;

abstract class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (Auth::guest()) {
            header('Location: ' . url('admin/login'));
            exit;
        }
    }

    protected function render(string $name, array $data = [], ?string $layout = 'admin'): string
    {
        return $this->view->render($name, array_merge([
            'siteName'  => setting('site_name', 'OpenBlog'),
            'user'      => Auth::user(),
            'stats'     => Post::stats(),
            'pendingCount' => (int)App::instance()->db->fetchColumn("SELECT COUNT(*) FROM `ob_comments` WHERE status='pending'"),
        ], $data), $layout);
    }

    protected function categories(): array
    {
        return Category::options();
    }

    /** 后台专用的 POST 校验 */
    protected function requireCsrf(): void
    {
        \App\Core\Security::abortIfInvalid();
    }
}
