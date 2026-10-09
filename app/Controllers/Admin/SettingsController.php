<?php
/**
 * OpenBlog - 后台站点设置
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Core\Settings;

class SettingsController extends AdminController
{
    private const KEYS = [
        'site_name', 'site_tagline', 'site_description', 'site_keywords',
        'site_logo', 'site_icp', 'admin_email',
        'posts_per_page', 'comment_enabled', 'comment_review', 'upload_max_size',
        'theme', 'accent_color', 'home_layout', 'show_toc', 'footer_text',
        'social_github', 'social_twitter', 'social_email', 'stat_code',
    ];

    public function index(): string
    {
        return $this->render('admin/settings/index', [
            'title'      => '站点设置',
            'settings'   => Settings::all(),
            'subscribers' => \App\Models\Subscriber::allRecent(100),
        ]);
    }

    public function update(): string
    {
        $this->requireCsrf();

        $pairs = [];
        foreach (self::KEYS as $key) {
            $value = $this->request->post($key, null);
            if ($value === null) {
                $pairs[$key] = str_starts_with($key, 'comment_') || $key === 'show_toc' ? '0' : '';
                continue;
            }
            $pairs[$key] = is_string($value) ? $value : (string)$value;
        }

        Settings::setMany($pairs);

        Session::flash('success', '设置已保存');
        return $this->redirect('admin/settings');
    }
}
