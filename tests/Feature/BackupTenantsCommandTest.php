<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TenantTestCase;

class BackupTenantsCommandTest extends TenantTestCase
{
    private string $centralSqlite;

    private string $backupRoot;

    private string $customPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->centralSqlite = database_path('backup-central-test.sqlite');
        $this->backupRoot = storage_path('app/testing-backup-rotation');
        $this->customPath = storage_path('app/testing-backup-cmd');

        File::deleteDirectory($this->backupRoot);
        File::deleteDirectory($this->customPath);

        File::put($this->centralSqlite, 'central-sqlite');
        config([
            'database.connections.sqlite.database' => $this->centralSqlite,
            'backup.directory' => 'testing-backup-rotation',
        ]);
    }

    protected function tearDown(): void
    {
        @unlink($this->centralSqlite);
        File::deleteDirectory($this->backupRoot);
        File::deleteDirectory($this->customPath);

        parent::tearDown();
    }

    public function test_writes_gzipped_copies_and_prunes_old_folders(): void
    {
        config([
            'backup.compress' => true,
            'backup.keep_days' => 14,
        ]);

        $old = $this->backupRoot.'/2020-01-01_020000';
        File::ensureDirectoryExists($old);
        File::put($old.'/central.sql.gz', 'old');

        $recentName = now()->subDays(2)->format('Y-m-d_His');
        $recent = $this->backupRoot.'/'.$recentName;
        File::ensureDirectoryExists($recent);
        File::put($recent.'/central.sql.gz', 'recent');

        $this->artisan('tenants:backup')->assertSuccessful();

        $this->assertDirectoryDoesNotExist($old);
        $this->assertDirectoryExists($recent);

        $created = collect(File::directories($this->backupRoot))
            ->reject(fn (string $dir) => basename($dir) === $recentName)
            ->values();

        $this->assertCount(1, $created);
        $this->assertFileExists($created[0].'/central.sql.gz');
        $this->assertSame('central-sqlite', gzdecode((string) file_get_contents($created[0].'/central.sql.gz')));
        $this->assertFileExists($created[0].'/tenant_demo.sql.gz');
        $this->assertNotFalse(gzdecode((string) file_get_contents($created[0].'/tenant_demo.sql.gz')));
    }

    public function test_custom_path_writes_uncompressed_when_disabled(): void
    {
        config([
            'backup.compress' => false,
            'backup.keep_days' => 14,
        ]);

        $this->artisan('tenants:backup', ['--path' => 'testing-backup-cmd'])
            ->assertSuccessful();

        $this->assertFileExists($this->customPath.'/central.sql');
        $this->assertSame('central-sqlite', File::get($this->customPath.'/central.sql'));
        $this->assertFileExists($this->customPath.'/tenant_demo.sql');
        $this->assertFileEquals(database_path('tenant_demo'), $this->customPath.'/tenant_demo.sql');
    }
}
