<?php

namespace Modules\Financials\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Concerns\AuthorizesCrud;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Credits\Entities\Abono;
use Modules\Financials\Actions\OpenCaja;
use Modules\Financials\Entities\Caja;
use Modules\Financials\Entities\Gasto;
use Modules\Financials\Http\Requests\OpenCajaRequest;
use Modules\Sales\Entities\Sale;
use RealRashid\SweetAlert\Facades\Alert;

class CashierController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('cash', ['history', 'arqueo'], extraUpdate: ['close']);
    }

    public function index()
    {
        $cajas = Caja::with(['user', 'sales', 'abonos'])->latest()->paginate(10);

        return view('financials::cajas.index', compact('cajas'));
    }

    public function create()
    {
        if (Caja::openForUser()) {
            return redirect()->route('sales.pos');
        }

        return view('financials::cajas.create');
    }

    public function store(OpenCajaRequest $request, OpenCaja $openCaja)
    {
        try {
            $openCaja->execute((int) auth()->id(), (float) $request->validated('monto_inicial'));
        } catch (BusinessRuleException $e) {
            Alert::error('Caja', $e->getMessage())->toToast();

            return back()->withInput();
        }

        Alert::success('Caja abierta', 'Ya podés vender. Esta caja es tuya hasta que la cierres.')->toToast();

        if (auth()->user()?->can('create sale')) {
            return redirect()->route('sales.pos');
        }

        return redirect()->route('financials.cajas.index');
    }

    public function arqueo($id)
    {
        $caja = Caja::findOrFail($id);

        // Ventas desglosadas por tipo de pago
        $sales = Sale::where('cash_id', $caja->id)->paid()->get();

        $salesCash = $sales->where('payment_type', 'efectivo')->sum('total');
        $salesQR = $sales->where('payment_type', 'qr')->sum('total');
        $salesCard = $sales->where('payment_type', 'tarjeta')->sum('total');
        $salesTransf = $sales->where('payment_type', 'transferencia')->sum('total');

        $totalSales = $sales->sum('total');

        // Otros movimientos
        $expenses = Gasto::where('cash_id', $caja->id)->get();
        $abonos = Abono::where('cash_id', $caja->id)->get();

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
        merge_currency_fields($request, ['monto_final']);

        $request->validate([
            'monto_final' => 'required|numeric|min:0',
        ]);

        \Log::info('Intentando cerrar caja ID: '.$id.' con monto: '.$request->monto_final);
        $caja = Caja::findOrFail($id);
        $caja->update([
            'closing_amount' => $request->monto_final,
            'closed_at' => now(),
            'status' => 0, // Closed
        ]);
        \Log::info('Caja ID: '.$id.' cerrada correctamente.');

        return redirect()->route('financials.cajas.index')->with('success', 'Caja cerrada con éxito');
    }

    public function history(Request $request)
    {
        $from = $request->input('from', now()->toDateString());
        $to = $request->input('to', now()->toDateString());

        if ($request->filled('month')) {
            $from = $request->month.'-01';
            $to = Carbon::parse($from)->endOfMonth()->toDateString();
        }

        $cajas = Caja::with(['user', 'sales', 'abonos', 'expenses'])
            ->whereDate('opened_at', '>=', $from)
            ->whereDate('opened_at', '<=', $to)
            ->orderByDesc('opened_at')
            ->get();

        $resumen = [
            'inicio' => $cajas->sum('opening_amount'),
            'efectivo' => 0,
            'transferencia' => 0,
            'qr' => 0,
            'tarjeta' => 0,
            'credito' => 0,
            'abonos' => $cajas->sum(fn ($c) => $c->abonos->sum('amount')),
            'egresos' => $cajas->sum(fn ($c) => $c->expenses->sum('amount')),
        ];

        foreach ($cajas as $caja) {
            $sales = $caja->sales->where('status', '!=', Sale::STATUS_VOIDED);
            $resumen['efectivo'] += $sales->where('payment_type', 'efectivo')->sum('total');
            $resumen['transferencia'] += $sales->where('payment_type', 'transferencia')->sum('total');
            $resumen['qr'] += $sales->where('payment_type', 'qr')->sum('total');
            $resumen['tarjeta'] += $sales->where('payment_type', 'tarjeta')->sum('total');
            $resumen['credito'] += $sales->where('payment_type', 'credito')->sum('total');
        }

        $expectedCash = $resumen['inicio'] + $resumen['efectivo'] + $resumen['abonos'] - $resumen['egresos'];

        return view('financials::cajas.history', compact('cajas', 'resumen', 'from', 'to', 'expectedCash'));
    }
}
