<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    public function publicUrl(string $path): string
    {
        return Storage::disk($this->publicDisk())->url($path);
    }

    public function temporaryUrl(string $disk, string $path, int $minutes = 15): string
    {
        $storage = Storage::disk($disk);

        if ((config("filesystems.disks.{$disk}.driver") ?? 'local') !== 's3') {
            return $storage->url($path);
        }

        try {
            $url = $storage->temporaryUrl($path, now()->addMinutes($minutes));
        } catch (\Throwable) {
            return $storage->url($path);
        }

        return $this->rewriteInternalHost($url);
    }

    public function settingUrl(?string $value): string
    {
        if (! $value) {
            return asset(config('media.placeholder'));
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $this->rewriteInternalHost($value);
        }

        $legacy = ltrim($value, '/');
        if (str_starts_with($legacy, 'storage/')) {
            return asset($legacy);
        }

        $private = config('media.private_disk');
        $public = $this->publicDisk();

        if ($this->existsOn($public, $legacy) || $this->existsOn($public, $value)) {
            return $this->publicUrl($legacy);
        }

        if ($this->existsOn($private, $legacy) || $this->existsOn($private, $value)) {
            return $this->temporaryUrl($private, $legacy, 60);
        }

        return asset($legacy);
    }

    public function rewriteInternalHost(string $url): string
    {
        $from = rtrim((string) config('media.internal_endpoint'), '/');
        $to = rtrim((string) config('media.public_endpoint'), '/');

        if ($from === '' || $to === '' || $from === $to) {
            return $url;
        }

        return str_replace($from, $to, $url);
    }

    private function publicDisk(): string
    {
        return (string) config('media.public_disk', 'public');
    }

    private function existsOn(string $disk, string $path): bool
    {
        try {
            return Storage::disk($disk)->exists($path);
        } catch (\Throwable) {
            return false;
        }
    }
}
