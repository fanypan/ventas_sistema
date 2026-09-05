<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $plans = Plan::listedOnLanding()->orderBy('sort_order')->get();
        $whatsapp = config('saas.whatsapp');
        $brand = config('saas.brand');

        return view('landing.index', compact('plans', 'whatsapp', 'brand'));
    }
}
