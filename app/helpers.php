<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('get_media_url')) {
    function get_media_url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        try {
            if (config('filesystems.default') === 'google' && Storage::disk('google')->exists($path)) {
                return Storage::disk('google')->url($path);
            }
        } catch (\Throwable $e) {
            // Failsafe fallback
        }

        return asset('storage/' . $path);
    }
}
