<?php
/**
 * OpenBlog - 后台媒体库
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Security;
use App\Core\Session;
use App\Core\Upload;
use App\Models\Media;

class MediaController extends AdminController
{
    public function index(): string
    {
        return $this->render('admin/media/index', [
            'title' => '媒体库',
            'files' => Media::recent(80),
        ]);
    }

    public function upload(): string
    {
        $this->requireCsrf();

        $file = $_FILES['file'] ?? null;
        if ($file === null || !is_array($file)) {
            Session::flash('error', '没有选择文件');
            return $this->redirect('admin/media');
        }

        [$ok, $result] = Upload::image($file);

        if (!$ok) {
            Session::flash('error', $result);
            return $this->redirect('admin/media');
        }

        $absolute = BASE_PATH . '/' . ltrim($result, '/');
        [$width, $height] = Upload::dimensions($absolute);

        Media::create([
            'user_id'       => Auth::id(),
            'filename'      => $result,
            'original_name' => $file['name'] ?? null,
            'mime_type'     => $file['type'] ?? null,
            'size'          => (int)($file['size'] ?? 0),
            'width'         => $width,
            'height'        => $height,
        ]);

        if ($this->request->isAjax()) {
            return $this->json(['ok' => true, 'url' => url($result), 'path' => $result]);
        }

        Session::flash('success', '上传成功');
        return $this->redirect('admin/media');
    }

    public function destroy(int $id): string
    {
        $this->requireCsrf();

        $media = Media::find($id);
        if ($media !== null) {
            $absolute = BASE_PATH . '/' . ltrim((string)$media->filename, '/');
            if (is_file($absolute)) {
                @unlink($absolute);
            }
            Media::deleteById($id);
        }

        Session::flash('success', '文件已删除');
        return $this->redirect('admin/media');
    }
}
