<?php

namespace App\Http\Controllers;

use App\Support\PosHelp;

class HelpController extends Controller
{
    public function index()
    {
        return view('admin.help', [
            'title' => 'Ayuda',
            'saleShortcuts' => PosHelp::shortcutsFor('sale'),
            'purchaseShortcuts' => PosHelp::shortcutsFor('purchase'),
        ]);
    }
}
