<?php
/**
 * OpenBlog - 后台页面管理
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Paginator;
use App\Core\Session;
use App\Models\Post;

class PagesController extends AdminController
{
    public function index(): string
    {
        $result = Post::adminList(['type' => 'page'], $this->page(), 15);

        return $this->render('admin/pages/index', [
            'title'     => '页面管理',
            'pages'     => $result['items'],
            'total'     => $result['total'],
            'paginator' => new Paginator($result, url('admin/pages'), 'page'),
        ]);
    }

    public function create(): string
    {
        return $this->render('admin/pages/form', [
            'title' => '新建页面',
            'page'  => null,
        ]);
    }

    public function store(): string
    {
        $this->requireCsrf();

        $check = $this->validate(['title' => ['required', 'max:255']]);
        if (!$check['ok']) {
            return $this->redirect('admin/pages/create');
        }

        $data = $this->collect();
        $data['user_id'] = Auth::id();
        $id = Post::savePost(null, $data);

        Session::flash('success', '页面已创建');
        return $this->redirect('admin/pages/edit/' . $id);
    }

    public function edit(int $id): string
    {
        $page = Post::findById($id);

        if ($page === null) {
            Session::flash('error', '页面不存在');
            return $this->redirect('admin/pages');
        }

        return $this->render('admin/pages/form', [
            'title' => '编辑：' . $page['title'],
            'page'  => $page,
        ]);
    }

    public function update(int $id): string
    {
        $this->requireCsrf();

        $check = $this->validate(['title' => ['required', 'max:255']]);
        if (!$check['ok']) {
            return $this->redirect('admin/pages/edit/' . $id);
        }

        Post::savePost($id, $this->collect());

        Session::flash('success', '页面已更新');
        return $this->redirect('admin/pages/edit/' . $id);
    }

    public function destroy(int $id): string
    {
        $this->requireCsrf();
        Post::deleteById($id);

        Session::flash('success', '页面已删除');
        return $this->redirect('admin/pages');
    }

    private function collect(): array
    {
        return [
            'title'         => $this->request->post('title', ''),
            'slug'          => $this->request->post('slug', '') ?: slugify((string)$this->request->post('title', '')),
            'content'       => $this->request->post('content', ''),
            'excerpt'       => $this->request->post('excerpt', ''),
            'cover_image'   => $this->request->post('cover_image', ''),
            'status'        => $this->request->post('status', 'draft'),
            'type'          => 'page',
            'allow_comment' => $this->request->bool('allow_comment'),
            'published_at'  => $this->request->post('published_at', '') ?: date('Y-m-d H:i:s'),
            'tags'          => [],
        ];
    }
}
