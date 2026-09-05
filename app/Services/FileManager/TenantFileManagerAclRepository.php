<?php

namespace App\Services\FileManager;

use Alexusmai\LaravelFileManager\Services\ACLService\ACLRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TenantFileManagerAclRepository implements ACLRepository
{
    public function getUserID()
    {
        return Auth::id() ?? 0;
    }

    public function getRules(): array
    {
        if (! Auth::check()) {
            return [];
        }

        if (config('filesystems.disks.filemanager.driver', 'local') === 'local') {
            File::ensureDirectoryExists(Storage::disk('filemanager')->path(''));
        }

        return [
            [
                'disk' => 'filemanager',
                'path' => '*',
                'access' => 2,
            ],
        ];
    }
}
