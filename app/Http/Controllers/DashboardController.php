<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\Sales\Entities\SaleDetail;

class DashboardController extends Controller
{
    public function welcome()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('welcome');
    }

    public function index(Request $request)
    {
        $x['title'] = 'Dashboard';
        $x['user']       = User::get();
        $x['role']       = Role::get();
        $x['permission'] = Permission::get();

        // Business metrics
        $today = now()->toDateString();

        $x['total_products']  = \Modules\Products\Entities\Product::where('status', 1)->count();
        $x['low_stock']       = \Modules\Products\Entities\Product::where('status', 1)->where('stock', '<=', 5)->count();
        $x['zero_stock']      = \Modules\Products\Entities\Product::where('status', 1)->where('stock', '<=', 0)->count();
        $x['total_customers'] = \Modules\Customers\Entities\Customer::count();

        $x['sales_today']     = \Modules\Sales\Entities\Sale::whereDate('created_at', $today)->sum('total');
        $x['sales_month']     = \Modules\Sales\Entities\Sale::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)->sum('total');
        $x['sales_count']     = \Modules\Sales\Entities\Sale::whereDate('created_at', $today)->count();

        $x['purchases_month'] = \Modules\Purchases\Entities\Purchase::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)->sum('total');

        $x['recent_sales'] = \Modules\Sales\Entities\Sale::with('customer')->latest()->take(8)->get();

        // Stock value
        $x['stock_value'] = \Modules\Products\Entities\Product::where('status', 1)
                              ->selectRaw('SUM(stock * price) as total')
                              ->value('total') ?? 0;

        $x['invest_value'] = \Modules\Products\Entities\Product::where('status', 1)
                               ->selectRaw('SUM(stock * cost) as total')
                               ->value('total') ?? 0;

        // Top 6 productos más vendidos del mes
        $x['top_products'] = SaleDetail::selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereHas('sale', function($q) {
                $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            })
            ->with('product:id,description')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(6)
            ->get();

        // Top 6 productos con stock más bajo (activos)
        $x['low_stock_products'] = \Modules\Products\Entities\Product::where('status', 1)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->take(6)
            ->get(['description', 'stock']);

        $x['chart_top_labels'] = $x['top_products']->map(fn ($p) => $p->product->description ?? ('#'.$p->product_id))->values();
        $x['chart_top_qty'] = $x['top_products']->pluck('total_qty')->map(fn ($v) => (float) $v)->values();
        $x['chart_low_labels'] = $x['low_stock_products']->pluck('description')->values();
        $x['chart_low_qty'] = $x['low_stock_products']->pluck('stock')->map(fn ($v) => (float) $v)->values();

        return view('admin.dashboard', $x);
    }
}
