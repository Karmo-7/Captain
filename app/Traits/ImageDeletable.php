<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ImageDeletable
{
    /**
     * Delete images from public disk using their URLs.
     */
    public function deleteImages(array $urls): void
    {
        foreach ($urls as $url) {
            // تحويل URL إلى المسار داخل التخزين
            $path = str_replace('/storage/', '', $url);
            Storage::disk('public')->delete($path);
        }
    }
}
