<?php
/**
 * OpenBlog - 分类页
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Paginator;
use App\Models\Category;
use App\Models\Post;

class CategoryController extends Controller
{
    public function show(string $slug): string
    {
        $category = Category::findBySlug($slug);

        if ($category === null) {
            http_response_code(404);
            return $this->view->render('errors/404', ['title' => '分类不存在', 'message' => '该分类不存在。'], 'main');
        }

        $page = $this->page();
        $result = Post::paginateByCategory((int)$category['id'], $page, $this->perPage());

        return $this->render('archive/listing', $this->shared([
            'title'      => $category['name'] . ' - 分类',
            'heading'    => $category['name'],
            'subtitle'   => $category['description'] ?: ('共 ' . $result['total'] . ' 篇文章'),
            'posts'      => $result['items'],
            'paginator'  => new Paginator($result, url('category/' . $slug)),
        ]));
    }
}
