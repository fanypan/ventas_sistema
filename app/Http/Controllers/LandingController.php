<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Routing\Controller;

class LandingController extends Controller
{
    public function index()
    {
        $plans = Plan::listedOnLanding()->orderBy('sort_order')->get();
        $whatsapp = config('saas.whatsapp');
        $brand = config('saas.brand');

        return view('landing.index', compact('plans', 'whatsapp', 'brand'));
    }
}
