<?php
/**
 * OpenBlog - 视图渲染
 *
 * 布局 + 片段 + 变量抽取，附带 HTML 转义助手 e()。
 */

declare(strict_types=1);

namespace App\Core;

class View
{
    private string $viewPath;

    /** @var array<string, mixed> */
    private static array $shared = [];

    public function __construct()
    {
        $this->viewPath = BASE_PATH . '/app/views';
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function shared(string $key, mixed $default = null): mixed
    {
        return self::$shared[$key] ?? $default;
    }

    public function exists(string $view): bool
    {
        return is_file($this->file($view));
    }

    private function file(string $view): string
    {
        return $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
    }

    public function render(string $view, array $data = [], ?string $layout = null): string
    {
        $file = $this->file($view);
        if (!is_file($file)) {
            throw new \RuntimeException("视图不存在：{$view}");
        }

        $data = array_merge(self::$shared, $data);

        $content = $this->renderFile($file, $data);

        $layout ??= $data['layout'] ?? null;
        if ($layout === null) {
            return $content;
        }

        $layoutFile = $this->file('layouts/' . $layout);
        if (!is_file($layoutFile)) {
            throw new \RuntimeException("布局不存在：{$layout}");
        }

        return $this->renderFile($layoutFile, array_merge($data, ['content' => $content]));
    }

    public function partial(string $view, array $data = []): string
    {
        return $this->renderFile($this->file($view), array_merge(self::$shared, $data));
    }

    private function renderFile(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string)ob_get_clean();
    }
}
