<?php
/**
 * OpenBlog - 后台用户管理
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Session;
use App\Models\User;

class UsersController extends AdminController
{
    public function index(): string
    {
        return $this->render('admin/users/index', [
            'title' => '用户管理',
            'users' => User::allWithStats(),
        ]);
    }

    public function store(): string
    {
        $this->requireCsrf();

        $check = $this->validate([
            'username' => ['required', 'min:3', 'max:50', 'unique:ob_users,username'],
            'email'    => ['required', 'email', 'unique:ob_users,email'],
            'password' => ['required', 'min:6'],
        ]);
        if (!$check['ok']) {
            return $this->redirect('admin/users');
        }

        User::createUser([
            'username'     => $this->request->post('username', ''),
            'email'        => $this->request->post('email', ''),
            'password'     => $this->request->post('password', ''),
            'display_name' => $this->request->post('display_name', ''),
            'role'         => $this->request->post('role', 'author'),
        ]);

        Session::flash('success', '用户已创建');
        return $this->redirect('admin/users');
    }

    public function edit(int $id): string
    {
        $user = User::find($id);
        if ($user === null) {
            Session::flash('error', '用户不存在');
            return $this->redirect('admin/users');
        }

        return $this->render('admin/users/edit', [
            'title'    => '编辑用户',
            'editUser' => $user->toArray(),
        ]);
    }

    public function update(int $id): string
    {
        $this->requireCsrf();

        $check = $this->validate([
            'username' => ['required', 'min:3', 'max:50', 'unique:ob_users,username,' . $id],
            'email'    => ['required', 'email', 'unique:ob_users,email,' . $id],
        ]);
        if (!$check['ok']) {
            return $this->redirect('admin/users/edit/' . $id);
        }

        User::updateUser($id, [
            'username'     => $this->request->post('username', ''),
            'email'        => $this->request->post('email', ''),
            'display_name' => $this->request->post('display_name', ''),
            'role'         => $this->request->post('role', 'author'),
            'status'       => $this->request->bool('status') ? 1 : 0,
            'bio'          => $this->request->post('bio', ''),
            'website'      => $this->request->post('website', ''),
            'password'     => $this->request->post('password', ''),
        ]);

        Session::flash('success', '用户已更新');
        return $this->redirect('admin/users');
    }

    public function destroy(int $id): string
    {
        $this->requireCsrf();

        if ($id === Auth::id()) {
            Session::flash('error', '不能删除当前登录账号');
            return $this->redirect('admin/users');
        }

        User::deleteById($id);
        Session::flash('success', '用户已删除');
        return $this->redirect('admin/users');
    }

    public function profile(): string
    {
        return $this->render('admin/users/profile', [
            'title' => '个人资料',
        ]);
    }

    public function updateProfile(): string
    {
        $this->requireCsrf();

        $id = Auth::id();

        $check = $this->validate([
            'username' => ['required', 'min:3', 'max:50', 'unique:ob_users,username,' . $id],
            'email'    => ['required', 'email', 'unique:ob_users,email,' . $id],
        ]);
        if (!$check['ok']) {
            return $this->redirect('admin/profile');
        }

        User::updateUser($id, [
            'username'     => $this->request->post('username', ''),
            'email'        => $this->request->post('email', ''),
            'display_name' => $this->request->post('display_name', ''),
            'bio'          => $this->request->post('bio', ''),
            'website'      => $this->request->post('website', ''),
            'avatar'       => $this->request->post('avatar', ''),
            'password'     => $this->request->post('password', ''),
        ]);

        Session::flash('success', '资料已更新');
        return $this->redirect('admin/profile');
    }
}
