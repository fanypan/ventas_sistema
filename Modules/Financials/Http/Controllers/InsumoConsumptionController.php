<?php

namespace Modules\Financials\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Financials\Entities\ConsumoInsumo;
use Modules\Financials\Entities\Insumo;
use Modules\Financials\Http\Requests\StoreInsumoConsumptionRequest;

class InsumoConsumptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:consume insumo');
    }

    public function index(Request $request): View
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

    public function store(StoreInsumoConsumptionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $insumo = Insumo::findOrFail($data['insumo_id']);

        if ($data['quantity'] > $insumo->stock) {
            return back()
                ->withInput()
                ->with('error', 'Stock insuficiente. Disponible: '.$insumo->stock);
        }

        DB::transaction(function () use ($data, $insumo) {
            ConsumoInsumo::create([
                'insumo_id' => $insumo->id,
                'quantity' => $data['quantity'],
                'user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);
            $insumo->decrement('stock', $data['quantity']);
        });

        return redirect()
            ->route('financials.insumos.consume')
            ->with('success', 'Consumo registrado. Stock actualizado.');
    }

    public function destroy($id): RedirectResponse
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
