<?php

namespace App\Core;

final class Uploader
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'];

    public static function optional(string $field, string $type, string $folder): ?string
    {
        if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed.');
        }

        $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowed = $type === 'image' ? self::IMAGE_EXTENSIONS : self::DOCUMENT_EXTENSIONS;
        if (!in_array($extension, $allowed, true)) {
            throw new \RuntimeException('Invalid file type.');
        }

        if ((int) $_FILES[$field]['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException('File must be 5MB or smaller.');
        }

        $targetDir = dirname(__DIR__, 2) . '/uploads/' . trim($folder, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $target = $targetDir . '/' . $fileName;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            throw new \RuntimeException('Could not store uploaded file.');
        }

        return 'uploads/' . trim($folder, '/') . '/' . $fileName;
    }
}
