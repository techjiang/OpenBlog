<?php
/**
 * OpenBlog - 后台分类管理
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Models\Category;

class CategoriesController extends AdminController
{
    public function index(): string
    {
        return $this->render('admin/categories/index', [
            'title'      => '分类管理',
            'categories' => Category::withCount(),
            'options'    => $this->categories(),
        ]);
    }

    public function store(): string
    {
        $this->requireCsrf();

        $check = $this->validate([
            'name' => ['required', 'max:80'],
        ]);
        if (!$check['ok']) {
            return $this->redirect('admin/categories');
        }

        $name = trim((string)$this->request->post('name', ''));
        $slug = trim((string)$this->request->post('slug', '')) ?: slugify($name);

        Category::create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $this->request->post('description', ''),
            'color'       => $this->request->post('color', '') ?: '#4f46e5',
            'parent_id'   => ($this->request->post('parent_id', '') !== '') ? (int)$this->request->post('parent_id') : null,
            'sort_order'  => (int)$this->request->post('sort_order', 0),
        ]);

        Session::flash('success', '分类已创建');
        return $this->redirect('admin/categories');
    }

    public function update(int $id): string
    {
        $this->requireCsrf();

        $check = $this->validate(['name' => ['required', 'max:80']]);
        if (!$check['ok']) {
            return $this->redirect('admin/categories');
        }

        $name = trim((string)$this->request->post('name', ''));

        Category::updateById($id, [
            'name'        => $name,
            'slug'        => trim((string)$this->request->post('slug', '')) ?: slugify($name),
            'description' => $this->request->post('description', ''),
            'color'       => $this->request->post('color', ''),
            'parent_id'   => ($this->request->post('parent_id', '') !== '') ? (int)$this->request->post('parent_id') : null,
            'sort_order'  => (int)$this->request->post('sort_order', 0),
        ]);

        Session::flash('success', '分类已更新');
        return $this->redirect('admin/categories');
    }

    public function destroy(int $id): string
    {
        $this->requireCsrf();

        \App\Core\App::instance()->db->query(
            'UPDATE `ob_posts` SET `category_id` = NULL WHERE `category_id` = :c',
            ['c' => $id]
        );
        Category::deleteById($id);

        Session::flash('success', '分类已删除，其下文章已移至未分类');
        return $this->redirect('admin/categories');
    }
}
