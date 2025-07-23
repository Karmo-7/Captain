<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ImageUploadable
{
    /**
     * Upload multiple images and return array of their URLs.
     *
     * @param \Illuminate\Http\UploadedFile[] $files
     * @param string $folder
     * @return array
     */
    public function uploadImages(array $files, string $folder): array
    {
        $paths = [];

        foreach ($files as $file) {
            $path = $file->store($folder, 'public');
            $paths[] = Storage::url($path);
        }

        return $paths;
    }
}
