<?php

namespace App\Services\Media;

use App\Exceptions\BusinessRuleException;
use App\Helpers\SettingHelper;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantLogoService
{
    public const DEFAULT_PATH = 'storage/logo.png';

    public function __construct(private ImageOptimizer $optimizer) {}

    public function put(Tenant $tenant, UploadedFile $file): string
    {
        if ($tenant->provisioned_at === null) {
            throw new BusinessRuleException('El comercio todavía se está aprovisionando.');
        }

        $encoded = $this->optimizer->encodePng($file);

        return $tenant->run(function () use ($encoded) {
            return $this->writeEncoded($encoded);
        });
    }

    public function storePending(UploadedFile $file): string
    {
        $encoded = $this->optimizer->encodePng($file);
        $relative = 'pending-logos/'.Str::uuid()->toString().'.png';

        File::ensureDirectoryExists(dirname($this->pendingAbsolute($relative)));
        File::put($this->pendingAbsolute($relative), $encoded->binary);

        return $relative;
    }

    public function applyPending(Tenant $tenant): void
    {
        $relative = $tenant->pending_logo_path;
        if (! is_string($relative) || $relative === '') {
            return;
        }

        if (! preg_match('/^pending-logos\/[a-z0-9-]+\.png$/', $relative)) {
            $this->forgetPending($tenant);

            return;
        }

        $absolute = $this->pendingAbsolute($relative);
        if (! is_file($absolute)) {
            $this->forgetPending($tenant);

            return;
        }

        $binary = File::get($absolute);
        $path = 'branding/'.Str::uuid()->toString().'.png';

        $tenant->run(function () use ($path, $binary) {
            $this->writeBinary($path, $binary);
        });

        File::delete($absolute);
        $this->forgetPending($tenant);
    }

    public function reset(Tenant $tenant): void
    {
        if ($tenant->provisioned_at === null) {
            throw new BusinessRuleException('El comercio todavía se está aprovisionando.');
        }

        $tenant->run(function () {
            $previous = Setting::where('key', 'app_logo')->value('value');
            $this->persistSetting(self::DEFAULT_PATH, 'png');
            $this->deleteCustom($previous);
        });
    }

    public function currentPath(Tenant $tenant): ?string
    {
        if ($tenant->provisioned_at === null) {
            return null;
        }

        try {
            return $tenant->run(fn () => SettingHelper::getValue('app_logo'));
        } catch (\Throwable) {
            return null;
        }
    }

    public static function isCustomPath(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return true;
        }

        $legacy = ltrim($value, '/');

        return ! str_starts_with($legacy, 'storage/');
    }

    public function forgetPending(Tenant $tenant): void
    {
        $relative = $tenant->pending_logo_path;
        $tenant->pending_logo_path = null;
        $tenant->save();

        if (is_string($relative) && preg_match('/^pending-logos\/[a-z0-9-]+\.png$/', $relative)) {
            File::delete($this->pendingAbsolute($relative));
        }
    }

    private function writeEncoded(EncodedImage $encoded): string
    {
        return $this->writeBinary($encoded->path, $encoded->binary);
    }

    private function writeBinary(string $path, string $binary): string
    {
        $disk = Storage::disk($this->disk());
        $previous = Setting::where('key', 'app_logo')->value('value');
        $disk->put($path, $binary, 'public');
        $this->persistSetting($path, pathinfo($path, PATHINFO_EXTENSION) ?: 'png');
        $this->deleteCustom($previous, $path);

        return $path;
    }

    private function persistSetting(string $path, string $ext): void
    {
        Setting::updateOrCreate(['key' => 'app_logo'], [
            'value' => $path,
            'name' => 'Application Logo',
            'type' => 'file',
            'ext' => $ext,
            'category' => 'information',
        ]);
    }

    private function deleteCustom(?string $path, ?string $except = null): void
    {
        if (! $path || $path === $except || ! self::isCustomPath($path)) {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk($this->disk())->delete(ltrim($path, '/'));
    }

    private function disk(): string
    {
        return (string) config('media.public_disk', 'public');
    }

    private function pendingAbsolute(string $relative): string
    {
        return base_path('storage/app/'.$relative);
    }
}
