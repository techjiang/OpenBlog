<?php
/**
 * OpenBlog - 后台登录 / 登出
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Security;
use App\Core\Session;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        if (Auth::check()) {
            return $this->redirect('admin');
        }

        return $this->view->render('admin/auth/login', [
            'title'   => '登录后台',
            'siteName'=> setting('site_name', 'OpenBlog'),
        ], 'blank');
    }

    public function login(): string
    {
        Security::abortIfInvalid();

        $account = trim((string)$this->request->post('account', ''));
        $password = (string)$this->request->post('password', '');
        $remember = $this->request->bool('remember');

        if ($account === '' || $password === '') {
            Session::flash('error', '请输入账号和密码');
            return $this->redirect('admin/login');
        }

        if (!Security::throttle('login:' . $this->request->ip(), 10, 300)) {
            Session::flash('error', '尝试次数过多，请稍后再试');
            return $this->redirect('admin/login');
        }

        if (!Auth::attempt($account, $password, $remember)) {
            Session::flash('error', '账号或密码错误');
            return $this->redirect('admin/login');
        }

        Session::flash('success', '欢迎回来');
        return $this->redirect('admin');
    }

    public function logout(): string
    {
        Auth::logout();
        Session::flash('success', '已安全退出');
        return $this->redirect('admin/login');
    }
}
