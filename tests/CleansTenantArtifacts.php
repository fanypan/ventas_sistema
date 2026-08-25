<?php

namespace Tests;

use Illuminate\Support\Facades\File;

trait CleansTenantArtifacts
{
    /** @var list<string> */
    protected array $tenantArtifactIds = [];

    protected function rememberTenantArtifact(string $tenantId): void
    {
        $this->tenantArtifactIds[] = $tenantId;
    }

    protected function cleanupTenantArtifacts(?string $tenantId = null): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }

        foreach (glob(database_path('tenant*')) ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        $ids = array_unique(array_filter(array_merge(
            $this->tenantArtifactIds,
            $tenantId ? [$tenantId] : [],
        )));

        foreach ($ids as $id) {
            $dir = base_path('storage/tenant'.$id);
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
    }
}
