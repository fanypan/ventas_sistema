<?php

namespace App\Http\Middleware;

use App\Services\Billing\PlanLimitService;
use Closure;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        $limits = app(PlanLimitService::class);
        if ($limits->hasFeature($feature)) {
            return $next($request);
        }

        $message = $limits->featureDeniedMessage($feature);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['error' => $message], 403);
        }

        Alert::error('Plan', $message)->toToast();

        if ($request->isMethod('GET')) {
            return redirect('/admin/dashboard');
        }

        return back();
    }
}
