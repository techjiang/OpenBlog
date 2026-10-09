<?php
/**
 * OpenBlog - 搜索页
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Paginator;
use App\Models\Post;

class SearchController extends Controller
{
    public function index(): string
    {
        $keyword = trim((string)$this->request->query('q', ''));
        $posts = [];
        $paginator = null;
        $total = 0;

        if ($keyword !== '') {
            $result = Post::paginateSearch($keyword, $this->page(), $this->perPage());
            $posts = $result['items'];
            $total = $result['total'];
            $paginator = new Paginator($result, url('search') . '?q=' . urlencode($keyword));
        }

        return $this->render('search/index', $this->shared([
            'title'     => $keyword !== '' ? "搜索：{$keyword}" : '搜索',
            'keyword'   => $keyword,
            'posts'     => $posts,
            'total'     => $total,
            'paginator' => $paginator,
        ]));
    }
}
