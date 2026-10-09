<?php
/**
 * OpenBlog - 文件上传
 */

declare(strict_types=1);

namespace App\Core;

class Upload
{
    private const ALLOWED_MIME = [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/gif'       => 'gif',
        'image/webp'      => 'webp',
        'image/svg+xml'   => 'svg',
        'image/x-icon'    => 'ico',
        'application/pdf' => 'pdf',
    ];

    /**
     * @return array{0:bool,1:string}
     */
    public static function image(array $file, string $subDir = ''): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return [false, '上传参数非法'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [false, self::errorMessage((int)$file['error'])];
        }

        if (($file['size'] ?? 0) > self::maxBytes()) {
            return [false, '文件超过大小限制'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_MIME)) {
            return [false, '不支持的文件类型：' . $mime];
        }

        $ext = self::ALLOWED_MIME[$mime];
        $dir = self::targetDir($subDir);
        $name = date('YmdHis') . '-' . substr(Security::randomName(16), 0, 10) . '.' . $ext;
        $destination = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [false, '文件写入失败，请检查目录权限'];
        }

        $relative = self::relativePath($destination);
        return [true, $relative];
    }

    public static function dimensions(string $absolutePath): array
    {
        $size = @getimagesize($absolutePath);
        return $size ? [(int)$size[0], (int)$size[1]] : [0, 0];
    }

    private static function maxBytes(): int
    {
        return (int)Settings::get('upload_max_size', '5') * 1024 * 1024;
    }

    private static function targetDir(string $subDir): string
    {
        $base = BASE_PATH . '/storage/uploads';
        $dir = $subDir !== '' ? $base . '/' . trim($subDir, '/') : $base;

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('无法创建上传目录：' . $dir);
        }
        return $dir;
    }

    private static function relativePath(string $absolute): string
    {
        return ltrim(str_replace(BASE_PATH, '', $absolute), '/');
    }

    private static function errorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => '文件超过服务器限制',
            UPLOAD_ERR_PARTIAL                        => '文件仅部分上传',
            UPLOAD_ERR_NO_FILE                        => '没有选择文件',
            UPLOAD_ERR_NO_TMP_DIR                     => '缺少临时目录',
            UPLOAD_ERR_CANT_WRITE                     => '磁盘写入失败',
            UPLOAD_ERR_EXTENSION                      => '上传被扩展阻止',
            default                                   => '未知上传错误',
        };
    }
}
