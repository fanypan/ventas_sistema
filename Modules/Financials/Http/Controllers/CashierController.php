<?php

namespace Modules\Financials\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Financials\Entities\Caja;

class CashierController extends Controller
{
    public function index()
    {
        $cajas = Caja::latest()->paginate(10);
        return view('financials::cajas.index', compact('cajas'));
    }

    public function create()
    {
        return view('financials::cajas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        Caja::create([
            'user_id' => auth()->id(),
            'opening_amount' => $request->monto_inicial,
            'closing_amount' => 0,
            'opened_at' => now(),
            'status' => 1, // Open
        ]);

        return redirect()->route('financials.cajas.index')->with('success', 'Caja abierta con éxito');
    }

    public function arqueo($id)
    {
        $caja = Caja::findOrFail($id);
        
        // Ventas desglosadas por tipo de pago
        $sales = \Modules\Sales\Entities\Sale::where('cash_id', $caja->id)->where('status', 1)->get();
        
        $salesCash = $sales->where('payment_type', 'efectivo')->sum('total');
        $salesQR = $sales->where('payment_type', 'qr')->sum('total');
        $salesCard = $sales->where('payment_type', 'tarjeta')->sum('total');
        $salesTransf = $sales->where('payment_type', 'transferencia')->sum('total');
        
        $totalSales = $sales->sum('total');

        // Otros movimientos
        $expenses = \Modules\Financials\Entities\Gasto::where('cash_id', $caja->id)->get();
        $abonos = \Modules\Credits\Entities\Abono::where('cash_id', $caja->id)->get();

        $totalExpenses = $expenses->sum('amount');
        $totalAbonos = $abonos->sum('amount');

        // Total esperado solo en EFECTIVO (para comparar con el conteo físico)
        $expectedCash = $caja->opening_amount + $salesCash + $totalAbonos - $totalExpenses;
        
        // Total general en sistema (todas las formas de pago)
        $expectedTotal = $caja->opening_amount + $totalSales + $totalAbonos - $totalExpenses;

        return view('financials::cajas.arqueo', compact(
            'caja', 
            'salesCash', 'salesQR', 'salesCard', 'salesTransf',
            'totalSales', 'totalExpenses', 'totalAbonos', 
            'expectedCash', 'expectedTotal'
        ));
    }

    public function close(Request $request, $id)
    {
        \Log::info("Intentando cerrar caja ID: " . $id . " con monto: " . $request->monto_final);
        $caja = Caja::findOrFail($id);
        $caja->update([
            'closing_amount' => $request->monto_final,
            'closed_at' => now(),
            'status' => 0, // Closed
        ]);
        \Log::info("Caja ID: " . $id . " cerrada correctamente.");

        return redirect()->route('financials.cajas.index')->with('success', 'Caja cerrada con éxito');
    }
}
