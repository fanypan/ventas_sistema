<?php

namespace App\Http\Controllers;

use App\Support\PosHelp;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        return view('admin.help', [
            'title' => 'Ayuda',
            'saleShortcuts' => PosHelp::shortcutsFor('sale'),
            'purchaseShortcuts' => PosHelp::shortcutsFor('purchase'),
        ]);
    }
}
