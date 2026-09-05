<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Nwidart\Modules\Facades\Module;

class ModuleController extends Controller
{
    public function index(Request $request): View
    {
        $x['title'] = 'Module';
        $x['enable'] = Module::allEnabled();
        $x['disable'] = Module::allDisabled();

        return view('admin.module', $x);
    }
}
