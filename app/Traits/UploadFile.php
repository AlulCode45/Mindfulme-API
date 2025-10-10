<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait UploadFile
{
    /**
     * Upload file baru dengan opsi hapus file lama jika sudah ada.
     *
     * @param  UploadedFile|string|null  $file
     * @param  string|null  $oldFilePath
     * @param  string  $directory
     * @param  string  $disk
     * @return string|null
     */
    public function uploadFile(UploadedFile|string|null $file, ?string $oldFilePath = null, string $directory = 'uploads', string $disk = 'public'): ?string
    {
        if (!$file) {
            return $oldFilePath;
        }

        // Jika file lama ada, hapus dulu
        if ($oldFilePath && Storage::disk($disk)->exists($oldFilePath)) {
            Storage::disk($disk)->delete($oldFilePath);
        }

        // Simpan file baru
        if ($file instanceof UploadedFile) {
            return $file->store($directory, $disk);
        }

        // Jika file berupa string (misal dari URL base64 atau sudah ada path-nya)
        return $file;
    }

    /**
     * Hapus file jika ada.
     *
     * @param  string|null  $filePath
     * @param  string  $disk
     * @return bool
     */
    public function deleteFile(?string $filePath, string $disk = 'public'): bool
    {
        if ($filePath && Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }
}
