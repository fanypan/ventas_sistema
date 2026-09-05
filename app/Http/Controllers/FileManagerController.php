<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{
    public function index()
    {
        $disk = Storage::disk('filemanager');

        if (config('filesystems.disks.filemanager.driver', 'local') === 'local') {
            File::ensureDirectoryExists($disk->path(''));
        }

        $x['title'] = 'Gestor de archivos';

        return view('admin.filemanager', $x);
    }
}
