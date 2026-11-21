<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

// Valid IDs (school ID / selfie / government ID uploaded at signup) are PRIVATE:
// stored on the "local" disk (storage/app/valid_ids), which no URL can reach, and
// served only through the admin route or the owner's own route.
//
// Older uploads were on the "public" disk (storage/app/public/valid_ids, reachable
// at /storage/valid_ids/...). `php artisan valid-ids:make-private --go` moves them.
// Until then, reading falls back to the public disk so nothing breaks in between.
// The path saved in users.valid_id ("valid_ids/abc.jpg") is the same on both disks.
class ValidIdFiles
{
    public const DISK        = 'local';
    public const LEGACY_DISK = 'public';
    public const DIR         = 'valid_ids';

    // Where this file is right now: 'local', 'public', or null if it's missing.
    public static function diskOf(?string $path): ?string
    {
        if (!$path) return null;
        if (Storage::disk(self::DISK)->exists($path)) return self::DISK;
        if (Storage::disk(self::LEGACY_DISK)->exists($path)) return self::LEGACY_DISK;
        return null;
    }

    // The file as an HTTP response (never cached by Cloudflare or the browser), or null.
    public static function response(?string $path)
    {
        $disk = self::diskOf($path);
        if (!$disk) return null;

        return Storage::disk($disk)->response($path, null, [
            'Cache-Control'          => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // Remove the file wherever it is (used when a user is deleted).
    public static function delete(?string $path): void
    {
        if (!$path) return;
        Storage::disk(self::DISK)->delete($path);
        Storage::disk(self::LEGACY_DISK)->delete($path);
    }
}
