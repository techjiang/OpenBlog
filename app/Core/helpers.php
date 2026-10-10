<?php
/**
 * OpenBlog - 全局助手函数
 */

declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;
use App\Core\Markdown;
use App\Core\Security;
use App\Core\Session;
use App\Core\Settings;
use App\Core\View;

if (!function_exists('e')) {
    /** HTML 转义 */
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /** 生成站内绝对 URL */
    function url(string $path = ''): string
    {
        $base = rtrim((string)App::conf('app.url', ''), '/');
        if ($base === '') {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
            $base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $scriptDir = str_replace('\\', '/', dirname((string)$_SERVER['SCRIPT_NAME']));
            if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '') {
                $base .= $scriptDir;
            }
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /** 生成静态资源 URL（带版本号防缓存） */
    function asset(string $path): string
    {
        $file = BASE_PATH . '/public/' . ltrim($path, '/');
        $version = is_file($file) ? substr(md5((string)filemtime($file)), 0, 8) : '1';
        return url(ltrim($path, '/')) . '?v=' . $version;
    }
}

if (!function_exists('view')) {
    /** 渲染视图 */
    function view(string $name, array $data = [], ?string $layout = 'main'): string
    {
        return (new View())->render($name, $data, $layout);
    }
}

if (!function_exists('partial')) {
    function partial(string $name, array $data = []): string
    {
        return (new View())->partial($name, $data);
    }
}

if (!function_exists('redirect')) {
    /** 重定向并终止 */
    function redirect(string $to): never
    {
        $to = str_starts_with($to, 'http') ? $to : url($to);
        header('Location: ' . $to, true, 302);
        exit;
    }
}

if (!function_exists('back')) {
    function back(string $fallback = ''): never
    {
        $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
        redirect($referer !== '' ? $referer : $fallback);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Security::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Security::token();
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::old($key, $default);
    }
}

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        return Settings::get($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return Auth::check();
    }
}

if (!function_exists('markdown')) {
    function markdown(string $text): string
    {
        return (new Markdown())->parse($text);
    }
}

if (!function_exists('slugify')) {
    /** 生成 URL 友好别名，保留中文 */
    function slugify(string $text): string
    {
        $text = trim($text);
        $slug = mb_strtolower($text);
        $slug = (string)preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug);
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'post-' . date('Ymd') . '-' . substr(md5($text), 0, 6);
        }
        return mb_substr($slug, 0, 120);
    }
}

if (!function_exists('human_date')) {
    function human_date(?string $datetime, string $format = 'Y-m-d'): string
    {
        if ($datetime === null || $datetime === '' || $datetime === '0000-00-00 00:00:00') {
            return '—';
        }
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $datetime): string
    {
        if ($datetime === null || $datetime === '') {
            return '—';
        }
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return '刚刚';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' 分钟前';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' 小时前';
        }
        if ($diff < 2592000) {
            return floor($diff / 86400) . ' 天前';
        }
        if ($diff < 31536000) {
            return floor($diff / 2592000) . ' 个月前';
        }
        return floor($diff / 31536000) . ' 年前';
    }
}

if (!function_exists('str_limit')) {
    function str_limit(string $value, int $limit = 100, string $end = '…'): string
    {
        return mb_strlen($value) <= $limit ? $value : mb_substr($value, 0, $limit) . $end;
    }
}

if (!function_exists('number_short')) {
    function number_short(int $number): string
    {
        if ($number < 1000) {
            return (string)$number;
        }
        if ($number < 1000000) {
            return round($number / 1000, 1) . 'k';
        }
        return round($number / 1000000, 1) . 'm';
    }
}

if (!function_exists('gravatar')) {
    function gravatar(string $email, int $size = 80): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }
}

if (!function_exists('is_active')) {
    /** 导航高亮判断 */
    function is_active(string $path, string $class = 'is-active'): string
    {
        $current = (string)($_SERVER['REQUEST_URI'] ?? '');
        $currentPath = parse_url($current, PHP_URL_PATH) ?: '/';
        $match = $path === '/' ? $currentPath === '/' : str_starts_with($currentPath, $path);
        return $match ? $class : '';
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        echo (new View())->render('errors/' . $code, ['title' => (string)$code, 'message' => $message], null);
        exit;
    }
}

if (!function_exists('status_label')) {
    /** 文章状态中文名 */
    function status_label(string $status): string
    {
        return match ($status) {
            'published' => '已发布',
            'draft'     => '草稿',
            'private'   => '私密',
            default     => $status,
        };
    }
}

if (!function_exists('comment_status_label')) {
    /** 评论状态中文名 */
    function comment_status_label(string $status): string
    {
        return match ($status) {
            'approved' => '已通过',
            'pending'  => '待审核',
            'spam'     => '垃圾',
            'trash'    => '回收站',
            default    => $status,
        };
    }
}

if (!function_exists('size_format')) {
    /** 字节数转可读大小 */
    function size_format(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);
        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('role_label')) {
    function role_label(string $role): string
    {
        return match ($role) {
            'admin'  => '管理员',
            'editor' => '编辑',
            'author' => '作者',
            default  => $role,
        };
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
