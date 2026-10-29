<?php
declare(strict_types=1);

namespace App\Helpers;

/** Upload ảnh dùng chung cho avatar và ảnh sản phẩm */
final class UploadHelper
{
    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    public const MAX_SIZE = 2 * 1024 * 1024; // 2MB

    /**
     * @return array{path:?string,error:?string} path tương đối so với public/ (vd uploads/products/abc.jpg)
     */
    public static function image(array $file, string $folder): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => 'Tải ảnh thất bại, vui lòng thử lại.'];
        }
        if ($file['size'] > self::MAX_SIZE) {
            return ['path' => null, 'error' => 'Ảnh vượt quá 2MB.'];
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime]) || @getimagesize($file['tmp_name']) === false) {
            return ['path' => null, 'error' => 'Chỉ chấp nhận ảnh jpg, png hoặc webp.'];
        }
        $dir = BASE_PATH . '/public/uploads/' . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . self::ALLOWED[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            return ['path' => null, 'error' => 'Không lưu được ảnh, kiểm tra quyền ghi thư mục uploads.'];
        }
        return ['path' => 'uploads/' . $folder . '/' . $name, 'error' => null];
    }

    /** Xóa ảnh cũ (chỉ xóa file nằm trong uploads/) */
    public static function remove(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/') && !str_contains($path, '..')) {
            $full = BASE_PATH . '/public/' . $path;
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}
