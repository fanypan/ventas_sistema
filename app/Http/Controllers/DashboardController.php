<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Customers\Entities\Customer;
use Modules\Products\Entities\Product;
use Modules\Purchases\Entities\Purchase;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleDetail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $x['title'] = 'Dashboard';
        $x['user'] = User::get();
        $x['role'] = Role::get();
        $x['permission'] = Permission::get();

        // Business metrics
        $today = now()->toDateString();

        $x['total_products'] = Product::active()->count();
        $x['low_stock'] = Product::active()->lowStock()->count();
        $x['zero_stock'] = Product::active()->zeroStock()->count();
        $x['total_customers'] = Customer::count();

        $x['sales_today'] = Sale::whereDate('created_at', $today)->sum('total');
        $x['sales_month'] = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->sum('total');
        $x['sales_count'] = Sale::whereDate('created_at', $today)->count();

        $x['purchases_month'] = Purchase::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->sum('total');

        $x['recent_sales'] = Sale::with('customer')->latest()->take(8)->get();

        // Stock value
        $x['stock_value'] = Product::active()
            ->selectRaw('SUM(stock * price) as total')
            ->value('total') ?? 0;

        $x['invest_value'] = Product::active()
            ->selectRaw('SUM(stock * cost) as total')
            ->value('total') ?? 0;

        // Top 6 productos más vendidos del mes
        $x['top_products'] = SaleDetail::selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereHas('sale', function ($q) {
                $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            })
            ->with('product:id,description')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(6)
            ->get();

        // Top 6 productos con stock más bajo (activos)
        $x['low_stock_products'] = Product::active()
            ->inStock()
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
