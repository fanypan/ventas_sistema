<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! config('observability.health_enabled')) {
            abort(404);
        }

        $checks = [
            'app' => 'ok',
            'database' => $this->database(),
            'storage' => $this->storage(),
        ];

        if ($this->shouldCheckRedis()) {
            $checks['redis'] = $this->redis();
        } else {
            $checks['redis'] = 'skipped';
        }

        if ($this->shouldCheckMinio()) {
            $checks['minio'] = $this->minio();
        } else {
            $checks['minio'] = 'skipped';
        }

        $ok = collect($checks)->every(fn (string $status) => in_array($status, ['ok', 'skipped'], true));

        return response()->json([
            'status' => $ok ? 'ok' : 'error',
            'checks' => $checks,
        ], $ok ? 200 : 503);
    }

    private function shouldCheckRedis(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if (! config('observability.health_check_redis')) {
            return false;
        }

        return in_array(config('cache.default'), ['redis'], true)
            || in_array(config('queue.default'), ['redis'], true)
            || in_array(config('session.driver'), ['redis'], true);
    }

    private function shouldCheckMinio(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if (! config('observability.health_check_minio')) {
            return false;
        }

        $disk = (string) config('media.public_disk', 'public');

        return (config("filesystems.disks.{$disk}.driver") ?? '') === 's3';
    }

    private function database(): string
    {
        try {
            DB::connection()->select('select 1');

            return 'ok';
        } catch (Throwable) {
            return 'error';
        }
    }

    private function redis(): string
    {
        try {
            Redis::connection()->ping();

            return 'ok';
        } catch (Throwable) {
            return 'error';
        }
    }

    private function storage(): string
    {
        $path = storage_path();

        return is_dir($path) && is_writable($path) ? 'ok' : 'error';
    }

    private function minio(): string
    {
        try {
            $disk = (string) config('media.public_disk');
            Storage::disk($disk)->exists('_health');

            return 'ok';
        } catch (Throwable) {
            return 'error';
        }
    }
}
