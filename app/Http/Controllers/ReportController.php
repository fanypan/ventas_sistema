<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleDetail;
use Modules\Purchases\Entities\Purchase;
use Modules\Financials\Entities\Expense;

class ReportController extends Controller
{
    public function index()
    {
        $lowStockCount = Product::where('status', 1)->where('stock', '<=', 5)->count();
        $productsCount = Product::where('status', 1)->count();
        return view('admin.reports.index', compact('lowStockCount', 'productsCount'));
    }

    public function productsPdf()
    {
        $products  = Product::with('category')->where('status', 1)->get();
        $inversion = $products->sum(fn($p) => $p->stock * $p->cost);
        $proyeccion= $products->sum(fn($p) => $p->stock * $p->price);
        $ganancia  = $proyeccion - $inversion;
        $utilidad  = ($inversion > 0) ? ($ganancia / $inversion) * 100 : 0;

        $pdf = Pdf::loadView('admin.reports.products_pdf', compact('products', 'inversion', 'proyeccion', 'utilidad'))
                  ->setPaper('a4', 'landscape');
        return $pdf->stream('reporte_productos.pdf');
    }

    public function productsExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ProductsExport, 'reporte_productos.xlsx');
    }

    /** Reporte: Stock mínimo / Productos con bajo inventario **/
    public function lowStockPdf(Request $request)
    {
        $threshold = (int) $request->get('threshold', 5);
        $products  = Product::with('category')
                        ->where('status', 1)
                        ->where('stock', '<=', $threshold)
                        ->orderBy('stock')
                        ->get();

        $pdf = Pdf::loadView('admin.reports.low_stock_pdf', compact('products', 'threshold'));
        return $pdf->stream('reporte_stock_minimo.pdf');
    }

    /** Reporte: Ventas por tipo de pago **/
    public function salesByPaymentPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();

        $sales = Sale::with('customer')
                     ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                     ->where('status', 1)
                     ->get();

        $byMethod = $sales->groupBy('payment_type')->map(fn($g) => [
            'count' => $g->count(),
            'total' => $g->sum('total'),
        ]);

        $pdf = Pdf::loadView('admin.reports.sales_by_payment_pdf',
                    compact('sales', 'byMethod', 'startDate', 'endDate'));
        return $pdf->stream('ventas_por_tipo_pago.pdf');
    }

    /** Reporte: Ventas por producto **/
    public function salesByProductPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();

        $details = SaleDetail::with('product', 'sale')
                    ->whereHas('sale', fn($q) =>
                        $q->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                          ->where('status', 1))
                    ->get()
                    ->groupBy('product_id');

        $pdf = Pdf::loadView('admin.reports.sales_by_product_pdf',
                    compact('details', 'startDate', 'endDate'));
        return $pdf->stream('ventas_por_producto.pdf');
    }

    /** Reporte: Ventas por rango de fechas **/
    public function salesPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();

        $sales = Sale::with('customer')
                     ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                     ->get();

        $pdf = Pdf::loadView('admin.reports.sales_pdf', compact('sales', 'startDate', 'endDate'));
        return $pdf->stream('reporte_ventas.pdf');
    }

    /** Reporte: Compras por rango de fechas **/
    public function purchasesPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();

        $purchases = Purchase::with('supplier')
                             ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                             ->get();

        $pdf = Pdf::loadView('admin.reports.purchases_pdf', compact('purchases', 'startDate', 'endDate'));
        return $pdf->stream('reporte_compras.pdf');
    }

    /** Arqueo de Caja **/
    public function cashPdf(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $salesTotal     = Sale::whereDate('created_at', $date)->where('status', 1)->sum('total');
        $purchasesTotal = Purchase::whereDate('created_at', $date)->sum('total');
        $expensesTotal  = class_exists('\Modules\Financials\Entities\Expense')
            ? Expense::whereDate('date', $date)->sum('amount')
            : 0;
        $net = $salesTotal - $purchasesTotal - $expensesTotal;

        $pdf = Pdf::loadView('admin.reports.cash_pdf',
                    compact('date', 'salesTotal', 'purchasesTotal', 'expensesTotal', 'net'));
        return $pdf->stream('arqueo_caja.pdf');
    }

    /** Estado de Resultados **/
    public function financialStatusPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? now()->toDateString();

        $salesTotal     = Sale::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                              ->where('status', 1)->sum('total');
        $purchasesTotal = Purchase::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->sum('total');
        $expensesTotal  = class_exists('\Modules\Financials\Entities\Expense')
            ? Expense::whereBetween('date', [$startDate, $endDate])->sum('amount')
            : 0;

        $utilidadBruta = $salesTotal - $purchasesTotal;
        $utilidadNeta  = $utilidadBruta - $expensesTotal;

        $pdf = Pdf::loadView('admin.reports.financial_status_pdf',
                    compact('startDate', 'endDate', 'salesTotal', 'purchasesTotal', 'expensesTotal', 'utilidadBruta', 'utilidadNeta'));
        return $pdf->stream('estado_resultados.pdf');
    }
}
