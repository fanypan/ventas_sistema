<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();

        return view('platform.plans.index', compact('plans'));
    }

    public function edit(Plan $plan)
    {
        return view('platform.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'price_yearly' => ['required', 'integer', 'min:0'],
            'max_users' => ['required', 'integer', 'min:0'],
            'max_cajas' => ['required', 'integer', 'min:0'],
            'sifen_documents_monthly' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_public'] = $request->boolean('is_public');
        $plan->update($data);

        return redirect()->route('platform.plans.index')->with('success', 'Plan actualizado.');
    }
}
