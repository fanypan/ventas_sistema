<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{
    public function index()
    {
        File::ensureDirectoryExists(Storage::disk('filemanager')->path(''));

        $x['title'] = 'Gestor de archivos';

        return view('admin.filemanager', $x);
    }
}
