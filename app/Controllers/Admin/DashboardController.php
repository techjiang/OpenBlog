<?php
/**
 * OpenBlog - 后台仪表盘
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Models\Comment;
use App\Models\Post;

class DashboardController extends AdminController
{
    public function index(): string
    {
        $db = App::instance()->db;

        $recentPosts = Post::withMeta()
            ->orderByRaw('p.created_at DESC')
            ->limit(8)
            ->get();

        $recentComments = $db->fetchAll(
            'SELECT c.*, p.title AS post_title FROM `ob_comments` c
             LEFT JOIN `ob_posts` p ON p.id = c.post_id
             ORDER BY c.created_at DESC LIMIT 8'
        );

        $monthly = $db->fetchAll(
            "SELECT DATE_FORMAT(published_at, '%Y-%m') AS ym, COUNT(*) AS total
             FROM `ob_posts` WHERE type = 'post'
             GROUP BY ym ORDER BY ym DESC LIMIT 12"
        );
        $monthly = array_reverse($monthly);

        return $this->render('admin/dashboard/index', [
            'title'          => '仪表盘',
            'recentPosts'    => $recentPosts,
            'recentComments' => $recentComments,
            'monthly'        => $monthly,
            'popular'        => Post::popular(5),
        ]);
    }

    public function clearCache(): string
    {
        $this->requireCsrf();

        $cacheDir = BASE_PATH . '/storage/cache';
        foreach (glob($cacheDir . '/*.cache') ?: [] as $file) {
            @unlink($file);
        }

        \App\Core\Session::flash('success', '缓存已清空');
        return $this->redirect('admin');
    }
}
