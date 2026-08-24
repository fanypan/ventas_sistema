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

        $root = Storage::disk('filemanager')->path('');
        File::ensureDirectoryExists($root);

        return [
            [
                'disk' => 'filemanager',
                'path' => '*',
                'access' => 2,
            ],
        ];
    }
}
