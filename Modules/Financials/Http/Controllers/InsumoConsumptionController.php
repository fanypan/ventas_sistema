<?php

namespace Modules\Financials\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Financials\Entities\ConsumoInsumo;
use Modules\Financials\Entities\Insumo;

class InsumoConsumptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:consume insumo');
    }

    public function index(Request $request)
    {
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $query = ConsumoInsumo::with(['insumo', 'user'])->latest()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $consumptions = $query->paginate(15)->withQueryString();
        $insumos = Insumo::orderBy('name')->get();

        return view('financials::insumos.consume', compact('consumptions', 'insumos', 'from', 'to'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $insumo = Insumo::findOrFail($request->insumo_id);

        if ($request->quantity > $insumo->stock) {
            return back()
                ->withInput()
                ->with('error', 'Stock insuficiente. Disponible: '.$insumo->stock);
        }

        DB::transaction(function () use ($request, $insumo) {
            ConsumoInsumo::create([
                'insumo_id' => $insumo->id,
                'quantity' => $request->quantity,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
            ]);
            $insumo->decrement('stock', $request->quantity);
        });

        return redirect()
            ->route('financials.insumos.consume')
            ->with('success', 'Consumo registrado. Stock actualizado.');
    }

    public function destroy($id)
    {
        $consumo = ConsumoInsumo::with('insumo')->findOrFail($id);

        DB::transaction(function () use ($consumo) {
            if ($consumo->insumo) {
                $consumo->insumo->increment('stock', $consumo->quantity);
            }
            $consumo->delete();
        });

        return redirect()
            ->route('financials.insumos.consume')
            ->with('success', 'Consumo anulado y stock restaurado.');
    }
}
