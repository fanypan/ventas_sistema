<?php

namespace Modules\Financials\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Financials\Entities\Caja;
use Modules\Financials\Entities\Gasto;
use Modules\Financials\Entities\Insumo;
use Modules\Financials\Http\Requests\StoreExpenseRequest;

class ExpenseController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('expense');
    }

    public function index(): View
    {
        $expenses = Gasto::with(['user', 'insumo'])->latest()->paginate(10);

        return view('financials::expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        $insumos = Insumo::orderBy('name', 'asc')->get();
        $openCash = Caja::openForUser();

        return view('financials::expenses.create', compact('insumos', 'openCash'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $cash = Caja::openForUser();
        if (! $cash) {
            return back()->with('error', 'Abrí tu caja para registrar egresos.')->withInput();
        }

        $insumoId = $data['insumo_id'] ?? null;

        if (($data['type'] ?? null) === 'insumo' && ($data['new_insumo'] ?? false)) {
            $insumo = Insumo::create([
                'name' => $data['description'],
                'price' => $data['amount'] / ($data['quantity'] ?? 1),
                'user_id' => auth()->id(),
            ]);
            $insumoId = $insumo->id;
        }

        Gasto::create([
            'user_id' => auth()->id(),
            'description' => $data['description'],
            'amount' => $data['amount'],
            'date' => now(),
            'cash_id' => $cash->id,
            'type' => $data['type'],
            'insumo_id' => $insumoId,
            'quantity' => $data['quantity'] ?? null,
        ]);

        if (($data['type'] ?? null) === 'insumo' && $insumoId) {
            $ins = Insumo::find($insumoId);
            $ins->increment('stock', $data['quantity'] ?? 0);
        }

        return redirect()->route('financials.expenses.index')->with('success', 'Egreso registrado con éxito');
    }
}
