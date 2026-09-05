<?php

namespace Modules\Credits\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Http\Responses\JsonEnvelope;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Credits\Actions\StoreAbono;
use Modules\Credits\Entities\Abono;
use Modules\Credits\Http\Requests\PayInstallmentRequest;
use Modules\Credits\Http\Requests\StoreAbonoRequest;
use Modules\Credits\Services\KardexService;
use Modules\Customers\Entities\Customer;
use Modules\Purchases\Entities\Purchase;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleInstallment;
use Modules\Suppliers\Entities\Supplier;

class CreditController extends Controller
{
    use AuthorizesCrud;

    public function __construct(private KardexService $kardex)
    {
        $this->authorizeCrud('credit');
        $this->middleware('permission:read credit')->only([
            'receivables',
            'customerKardex',
            'customerKardexPdf',
            'printReceipt',
        ]);
        $this->middleware('permission:read supplier')->only([
            'payables',
            'supplierKardex',
            'supplierKardexPdf',
        ]);
        $this->middleware('permission:create credit|update purchase')->only(['storeAbono']);
        $this->middleware('permission:create credit')->only(['payInstallment']);
    }

    public function receivables(Request $request): View
    {
        $term = trim((string) $request->get('customer'));
        $showAll = $request->boolean('show_all');
        $sales = null;
        $matchingSales = collect();
        $selectedSaleId = $request->integer('selected_sale');
        $shouldOpenSelector = false;
        $autoOpenSaleId = null;

        if ($term !== '') {
            $matchingSales = Sale::with(['customer', 'installments', 'abonos', 'details.product'])
                ->credit()
                ->where(function ($subQuery) use ($term) {
                    $subQuery->whereHas('customer', function ($customerQuery) use ($term) {
                        $customerQuery->where('name', 'like', "%{$term}%")
                            ->orWhere('nit', 'like', "%{$term}%");
                    });

                    if (is_numeric($term)) {
                        $subQuery->orWhere('id', (int) $term);
                    }
                })
                ->latest()
                ->get();

            if ($matchingSales->count() === 1) {
                $selectedSale = $matchingSales->first();
                $sales = collect([$selectedSale]);
                if (($selectedSale->installments_count ?? 0) > 0) {
                    $autoOpenSaleId = $selectedSale->id;
                }
            } elseif ($matchingSales->count() > 1) {
                if ($selectedSaleId) {
                    $selectedSale = $matchingSales->firstWhere('id', $selectedSaleId);
                    if ($selectedSale) {
                        $sales = collect([$selectedSale]);
                        if (($selectedSale->installments_count ?? 0) > 0) {
                            $autoOpenSaleId = $selectedSale->id;
                        }
                    } else {
                        $sales = collect();
                        $shouldOpenSelector = true;
                    }
                } else {
                    $sales = collect();
                    $shouldOpenSelector = true;
                }
            } else {
                $sales = collect();
            }
        } elseif ($showAll) {
            $sales = Sale::with(['customer', 'installments', 'abonos', 'details.product'])
                ->credit()
                ->latest()
                ->paginate(10)
                ->appends($request->only('customer', 'show_all'));
        }

        return view('credits::receivables', compact(
            'sales',
            'term',
            'showAll',
            'matchingSales',
            'selectedSaleId',
            'shouldOpenSelector',
            'autoOpenSaleId'
        ));
    }

    public function payables(): View
    {
        $purchases = Purchase::with('supplier')
            ->credit()
            ->latest()
            ->paginate(10);

        return view('credits::payables', compact('purchases'));
    }

    public function customerKardex(Request $request, $id): View
    {
        $customer = Customer::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->kardex->customerMovements($customer->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;

        return view('credits::kardex_customer', compact('customer', 'movements', 'from', 'to', 'saldo'));
    }

    public function customerKardexPdf(Request $request, $id): Response
    {
        $customer = Customer::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->kardex->customerMovements($customer->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView(
            'credits::pdf.kardex_customer',
            compact('customer', 'movements', 'from', 'to', 'saldo', 'settings')
        )->setPaper('a4', 'portrait');

        return $pdf->stream('estado_cuenta_cliente_'.$customer->id.'.pdf');
    }

    public function supplierKardex(Request $request, $id): View
    {
        $supplier = Supplier::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->kardex->supplierMovements($supplier->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;

        return view('credits::kardex_supplier', compact('supplier', 'movements', 'from', 'to', 'saldo'));
    }

    public function supplierKardexPdf(Request $request, $id): Response
    {
        $supplier = Supplier::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->kardex->supplierMovements($supplier->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView(
            'credits::pdf.kardex_supplier',
            compact('supplier', 'movements', 'from', 'to', 'saldo', 'settings')
        )->setPaper('a4', 'portrait');

        return $pdf->stream('estado_cuenta_proveedor_'.$supplier->id.'.pdf');
    }

    public function storeAbono(StoreAbonoRequest $request, StoreAbono $storeAbono): JsonResponse|RedirectResponse
    {
        try {
            $abono = $storeAbono->execute($request->validated(), (int) auth()->id());
        } catch (BusinessRuleException $e) {
            return $this->abonoFailure($request, $e->getMessage());
        }

        return $this->abonoSuccess($request, $abono);
    }

    public function payInstallment(PayInstallmentRequest $request, StoreAbono $storeAbono): JsonResponse|RedirectResponse
    {
        $installment = SaleInstallment::find($request->validated('installment_id'));

        if (! $installment) {
            return back()->with('error', 'Cuota no encontrada.');
        }

        if ($installment->status == 1) {
            return back()->with('error', 'Esta cuota ya ha sido pagada.');
        }

        $amountToPay = (float) $installment->amount - (float) $installment->paid_amount;

        try {
            $abono = $storeAbono->execute([
                'abonable_id' => $installment->sale_id,
                'abonable_type' => Sale::class,
                'amount' => $amountToPay,
                'payment_method' => $request->validated('payment_method'),
                'installment_number' => $installment->installment_number,
            ], (int) auth()->id());
        } catch (BusinessRuleException $e) {
            return $this->abonoFailure($request, $e->getMessage());
        }

        return $this->abonoSuccess($request, $abono);
    }

    public function create(): View
    {
        return view('credits::create');
    }

    public function show($id): View
    {
        return view('credits::show');
    }

    public function edit($id): View
    {
        return view('credits::edit');
    }

    public function printReceipt($id): Response
    {
        $abono = Abono::with('user', 'abonable')->findOrFail($id);
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView('credits::pdf.receipt', compact('abono', 'settings'))
            ->setPaper('a5', 'landscape');

        return $pdf->stream('recibo_'.$abono->id.'.pdf');
    }

    private function abonoSuccess(Request $request, Abono $abono): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return JsonEnvelope::success('¡Pago registrado con éxito!', ['abono_id' => $abono->id]);
        }

        return back()->with('success', '¡Pago registrado con éxito!');
    }

    private function abonoFailure(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return JsonEnvelope::error($message, null, 422);
        }

        return back()->with('error', $message);
    }
}
