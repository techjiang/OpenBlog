<?php
/**
 * OpenBlog - 标签页
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Paginator;
use App\Models\Post;
use App\Models\Tag;

class TagController extends Controller
{
    public function show(string $slug): string
    {
        $tag = Tag::findBySlug($slug);

        if ($tag === null) {
            http_response_code(404);
            return $this->view->render('errors/404', ['title' => '标签不存在', 'message' => '该标签不存在。'], 'main');
        }

        $page = $this->page();
        $result = Post::paginateByTag((int)$tag['id'], $page, $this->perPage());

        return $this->render('archive/listing', $this->shared([
            'title'     => '#' . $tag['name'] . ' - 标签',
            'heading'   => '#' . $tag['name'],
            'subtitle'  => '共 ' . $result['total'] . ' 篇文章',
            'posts'     => $result['items'],
            'paginator' => new Paginator($result, url('tag/' . $slug)),
        ]));
    }
}
