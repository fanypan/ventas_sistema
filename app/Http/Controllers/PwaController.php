<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Services\Pwa\TenantPwa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(TenantPwa $pwa): JsonResponse
    {
        return response()->json($pwa->manifest(), 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function serviceWorker(TenantPwa $pwa): Response
    {
        $script = view('pwa.service-worker', [
            'cacheName' => $pwa->cacheName(),
            'offlineUrl' => url('/offline'),
        ])->render();

        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function favicon(TenantPwa $pwa): Response
    {
        return $this->pngResponse($pwa->iconPng(32));
    }

    public function icon(int $size, TenantPwa $pwa): Response
    {
        abort_unless(in_array($size, TenantPwa::ICON_SIZES, true), 404);

        return $this->pngResponse($pwa->iconPng($size));
    }

    private function pngResponse(string $binary): Response
    {
        return response($binary, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function offline(): Response
    {
        return response()->view('pwa.offline', [
            'appName' => SettingHelper::getValue('app_name') ?: config('app.name'),
        ]);
    }
}
