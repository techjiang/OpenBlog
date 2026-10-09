<?php
/**
 * OpenBlog - 首页
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Paginator;
use App\Models\Post;
use App\Models\Subscriber;

class HomeController extends Controller
{
    public function handle(int $page = 1): string
    {
        $page = max(1, $page);
        $perPage = $this->perPage();

        $result = Post::paginatePublished($page, $perPage);
        $paginator = new Paginator($result, url('/'));

        $hero = null;
        if ($page === 1) {
            $featured = Post::paginateFeatured(1, 1);
            $hero = $featured['items'][0] ?? null;
        }

        return $this->render('home/index', $this->shared([
            'title'     => setting('site_name', 'OpenBlog') . ($page > 1 ? " - 第 {$page} 页" : ''),
            'posts'     => $result['items'],
            'paginator' => $paginator,
            'hero'      => $hero,
            'page'      => $page,
        ]));
    }

    public function index(int $page = 1): string
    {
        return $this->handle($page);
    }

    public function subscribe(): string
    {
        \App\Core\Security::abortIfInvalid();
        $email = trim((string)$this->request->input('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', '邮箱格式不正确');
            return $this->back();
        }

        Subscriber::subscribe($email);
        $this->flash('success', '订阅成功，感谢关注');
        return $this->back();
    }
}
