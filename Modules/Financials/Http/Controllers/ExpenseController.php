<?php

namespace Modules\Financials\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Financials\Entities\Gasto;
use Modules\Financials\Entities\Insumo;
use Modules\Financials\Entities\Caja;
use App\Http\Controllers\Concerns\AuthorizesCrud;

class ExpenseController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('expense', extraCreate: ['searchInsumo']);
    }

    public function index()
    {
        $expenses = Gasto::with(['user', 'insumo'])->latest()->paginate(10);
        return view('financials::expenses.index', compact('expenses'));
    }

    public function create()
    {
        $insumos = Insumo::orderBy('name', 'asc')->get();
        $openCash = Caja::where('status', 1)->first();
        return view('financials::expenses.create', compact('insumos', 'openCash'));
    }

    public function store(Request $request)
    {
        merge_currency_fields($request, ['amount']);

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:gasto,insumo',
            'insumo_id' => 'required_if:type,insumo|nullable|exists:insumos,id',
            'quantity' => 'required_if:type,insumo|nullable|numeric|min:0.01',
            'new_insumo' => 'nullable|boolean',
        ]);

        $cash = Caja::where('status', 1)->first();
        if (!$cash) {
            return back()->with('error', 'Debe tener una caja abierta para registrar egresos.')->withInput();
        }

        $insumo_id = $request->insumo_id;

        // If it's an insumo and user wants to create a new one on the fly (or if provided a name instead of ID)
        if ($request->type == 'insumo' && $request->new_insumo) {
            $insumo = Insumo::create([
                'name' => $request->description,
                'price' => $request->amount / ($request->quantity ?: 1),
                'user_id' => auth()->id(),
            ]);
            $insumo_id = $insumo->id;
        }

        Gasto::create([
            'user_id' => auth()->id(),
            'description' => $request->description,
            'amount' => $request->amount,
            'date' => now(),
            'cash_id' => $cash->id,
            'type' => $request->type,
            'insumo_id' => $insumo_id,
            'quantity' => $request->quantity,
        ]);

        // Update stock of insumo if applicable
        if ($request->type == 'insumo' && $insumo_id) {
            $ins = Insumo::find($insumo_id);
            $ins->increment('stock', $request->quantity ?: 0);
        }

        return redirect()->route('financials.expenses.index')->with('success', 'Egreso registrado con éxito');
    }

    public function searchInsumo(Request $request)
    {
        $term = $request->term;
        $insumos = Insumo::where('name', 'LIKE', "%$term%")->get();
        return response()->json($insumos);
    }
}
