<?php
/**
 * OpenBlog - 独立页面
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Markdown;
use App\Models\Post;

class PageController extends Controller
{
    public function show(string $slug): string
    {
        $page = Post::findPageBySlug($slug);

        if ($page === null) {
            http_response_code(404);
            return $this->view->render('errors/404', ['title' => '页面不存在', 'message' => '该页面不存在。'], 'main');
        }

        $html = (string)($page['content_html'] ?? '');
        if ($html === '') {
            $html = (new Markdown())->parse((string)$page['content']);
        }

        return $this->render('page/show', $this->shared([
            'title'       => $page['title'],
            'page'        => $page,
            'html'        => $html,
            'description' => $page['excerpt'] ?? '',
        ]));
    }
}
