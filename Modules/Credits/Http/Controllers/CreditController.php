<?php

namespace Modules\Credits\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\Concerns\AuthorizesCrud;

class CreditController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
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

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function receivables(Request $request)
    {
        $term = trim((string) $request->get('customer'));
        $showAll = $request->boolean('show_all');
        $sales = null;
        $matchingSales = collect();
        $selectedSaleId = $request->integer('selected_sale');
        $shouldOpenSelector = false;
        $autoOpenSaleId = null;

        if ($term !== '') {
            // Status 2: Credit/Pending
            $matchingSales = \Modules\Sales\Entities\Sale::with(['customer', 'installments', 'abonos', 'details.product'])
                ->where('status', 2)
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
            $sales = \Modules\Sales\Entities\Sale::with(['customer', 'installments', 'abonos', 'details.product'])
                ->where('status', 2)
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

    public function payables()
    {
        // Status 2: Credit/Pending
        $purchases = \Modules\Purchases\Entities\Purchase::with('supplier')
            ->where('status', 2)
            ->latest()
            ->paginate(10);
            
        return view('credits::payables', compact('purchases'));
    }

    public function customerKardex(Request $request, $id)
    {
        $customer = \Modules\Customers\Entities\Customer::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->buildCustomerMovements($customer->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;

        return view('credits::kardex_customer', compact('customer', 'movements', 'from', 'to', 'saldo'));
    }

    public function customerKardexPdf(Request $request, $id)
    {
        $customer = \Modules\Customers\Entities\Customer::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->buildCustomerMovements($customer->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;
        $settings = \App\Models\Setting::all()->pluck('value', 'key');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'credits::pdf.kardex_customer',
            compact('customer', 'movements', 'from', 'to', 'saldo', 'settings')
        )->setPaper('a4', 'portrait');

        return $pdf->stream('estado_cuenta_cliente_'.$customer->id.'.pdf');
    }

    public function supplierKardex(Request $request, $id)
    {
        $supplier = \Modules\Suppliers\Entities\Supplier::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->buildSupplierMovements($supplier->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;

        return view('credits::kardex_supplier', compact('supplier', 'movements', 'from', 'to', 'saldo'));
    }

    public function supplierKardexPdf(Request $request, $id)
    {
        $supplier = \Modules\Suppliers\Entities\Supplier::findOrFail($id);
        $from = $request->input('from');
        $to = $request->input('to');
        $movements = $this->buildSupplierMovements($supplier->id, $from, $to);
        $saldo = $movements->last()['saldo'] ?? 0;
        $settings = \App\Models\Setting::all()->pluck('value', 'key');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'credits::pdf.kardex_supplier',
            compact('supplier', 'movements', 'from', 'to', 'saldo', 'settings')
        )->setPaper('a4', 'portrait');

        return $pdf->stream('estado_cuenta_proveedor_'.$supplier->id.'.pdf');
    }

    private function buildCustomerMovements(int $customerId, $from = null, $to = null)
    {
        $sales = \Modules\Sales\Entities\Sale::with(['abonos.user', 'creator'])
            ->where('customer_id', $customerId)
            ->where(function ($q) {
                $q->where('payment_type', 'credito')
                    ->orWhereIn('status', [2, 3])
                    ->orWhereHas('abonos');
            })
            ->orderBy('created_at')
            ->get();

        $rows = collect();
        foreach ($sales as $sale) {
            $rows->push([
                'date' => $sale->created_at,
                'type' => 'factura',
                'ref' => $sale->id,
                'description' => 'Venta #' . $sale->id,
                'cargo' => (float) $sale->total,
                'abono' => 0,
                'user' => $sale->creator->name ?? '-',
                'abono_id' => null,
            ]);
            foreach ($sale->abonos as $abono) {
                $rows->push([
                    'date' => $abono->payment_date ?? $abono->created_at,
                    'type' => 'abono',
                    'ref' => $sale->id,
                    'description' => 'Abono venta #' . $sale->id . ($abono->payment_method ? ' (' . $abono->payment_method . ')' : ''),
                    'cargo' => 0,
                    'abono' => (float) $abono->amount,
                    'user' => $abono->user->name ?? '-',
                    'abono_id' => $abono->id,
                ]);
            }
        }

        $rows = $rows->sortBy(function ($r) {
            return \Carbon\Carbon::parse($r['date'])->timestamp;
        })->values();

        if ($from) {
            $rows = $rows->filter(fn ($r) => \Carbon\Carbon::parse($r['date'])->toDateString() >= $from)->values();
        }
        if ($to) {
            $rows = $rows->filter(fn ($r) => \Carbon\Carbon::parse($r['date'])->toDateString() <= $to)->values();
        }

        $saldo = 0;
        return $rows->map(function ($row) use (&$saldo) {
            $saldo += $row['cargo'] - $row['abono'];
            $row['saldo'] = $saldo;
            return $row;
        });
    }

    private function buildSupplierMovements(int $supplierId, $from = null, $to = null)
    {
        $purchases = \Modules\Purchases\Entities\Purchase::with(['abonos.user', 'creator'])
            ->where('supplier_id', $supplierId)
            ->where(function ($q) {
                $q->where('status', 2)->orWhereHas('abonos');
            })
            ->orderBy('created_at')
            ->get();

        $rows = collect();
        foreach ($purchases as $purchase) {
            $rows->push([
                'date' => $purchase->created_at,
                'type' => 'compra',
                'ref' => $purchase->id,
                'description' => 'Compra #' . $purchase->id,
                'cargo' => (float) $purchase->total,
                'abono' => 0,
                'user' => $purchase->creator->name ?? '-',
                'abono_id' => null,
            ]);
            foreach ($purchase->abonos as $abono) {
                $rows->push([
                    'date' => $abono->payment_date ?? $abono->created_at,
                    'type' => 'pago',
                    'ref' => $purchase->id,
                    'description' => 'Pago compra #' . $purchase->id . ($abono->payment_method ? ' (' . $abono->payment_method . ')' : ''),
                    'cargo' => 0,
                    'abono' => (float) $abono->amount,
                    'user' => $abono->user->name ?? '-',
                    'abono_id' => $abono->id,
                ]);
            }
        }

        $rows = $rows->sortBy(function ($r) {
            return \Carbon\Carbon::parse($r['date'])->timestamp;
        })->values();

        if ($from) {
            $rows = $rows->filter(fn ($r) => \Carbon\Carbon::parse($r['date'])->toDateString() >= $from)->values();
        }
        if ($to) {
            $rows = $rows->filter(fn ($r) => \Carbon\Carbon::parse($r['date'])->toDateString() <= $to)->values();
        }

        $saldo = 0;
        return $rows->map(function ($row) use (&$saldo) {
            $saldo += $row['cargo'] - $row['abono'];
            $row['saldo'] = $saldo;
            return $row;
        });
    }

    public function storeAbono(Request $request)
    {
        \Log::info("=== ABONO: Petición recibida ===", $request->all());
        
        $request->validate([
            'abonable_id'   => 'required|integer',
            'abonable_type' => 'required|string',
            'amount'        => 'required|numeric|min:0.01',
            'payment_method'=> 'required|string',
        ]);

        // 0. Find active Caja
        $caja = \Modules\Financials\Entities\Caja::where('status', 1)->first();
        if (!$caja) {
            \Log::warning("ABONO: No hay caja abierta");
            return back()->with('error', 'No hay ninguna caja abierta. Debe abrir la caja para registrar cobros.');
        }
        \Log::info("ABONO: Caja activa ID=" . $caja->id);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Create Abono record
            $abono = \Modules\Credits\Entities\Abono::create([
                'abonable_id'        => $request->abonable_id,
                'abonable_type'      => $request->abonable_type,
                'amount'             => $request->amount,
                'payment_method'     => $request->payment_method,
                'payment_date'       => now(),
                'reference'          => $request->reference ?? null,
                'note'               => $request->note ?? null,
                'received_amount'    => $request->received_amount ?? null,
                'installment_number' => $request->installment_number ?? null,
                'user_id'            => auth()->id(),
                'cash_id'            => $caja->id,
            ]);
            \Log::info("ABONO: Creado con ID=" . $abono->id);

            // 2. Find the parent model (Sale)
            // Use app() to safely instantiate from a string class name
            $modelClass = $request->abonable_type;
            $model = $modelClass::find($request->abonable_id);

            if (!$model) {
                throw new \Exception("No se encontró el modelo {$modelClass} con ID {$request->abonable_id}");
            }

            // 3. Distribute payment across pending installments if applicable
            if ($modelClass === 'Modules\Sales\Entities\Sale' && $model->installments_count > 0) {
                $remainingToApply = floatval($request->amount);
                $installments = $model->installments()
                    ->where('status', 0)
                    ->orderBy('installment_number')
                    ->get();

                \Log::info("ABONO: Distribuyendo " . $remainingToApply . " en " . count($installments) . " cuotas pendientes");

                foreach ($installments as $inst) {
                    if ($remainingToApply <= 0) break;

                    $pendingOnThis = floatval($inst->amount) - floatval($inst->paid_amount);
                    if ($remainingToApply >= $pendingOnThis) {
                        $inst->paid_amount = $inst->amount;
                        $inst->status = 1;
                        $inst->paid_at = now();
                        $remainingToApply -= $pendingOnThis;
                    } else {
                        $inst->paid_amount = floatval($inst->paid_amount) + $remainingToApply;
                        $remainingToApply = 0;
                    }
                    $inst->save();
                    \Log::info("ABONO: Cuota #{$inst->installment_number} actualizada: pagado={$inst->paid_amount}, status={$inst->status}");
                }
            }

            // 4. Check if fully paid
            $pending = $model->pending_balance();
            \Log::info("ABONO: Saldo pendiente tras el pago: {$pending}");
            if ($pending <= 0) {
                $model->status = 1; // Mark as fully paid
                $model->save();
                \Log::info("ABONO: Venta #{$model->id} marcada como PAGADA");
            }

            \Illuminate\Support\Facades\DB::commit();
            \Log::info("ABONO: Transacción completada con éxito");
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '¡Pago registrado con éxito!',
                    'abono_id' => $abono->id,
                ]);
            }
            return back()->with('success', '¡Pago registrado con éxito!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error("ABONO: Error - " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar pago: ' . $e->getMessage(),
                ], 422);
            }
            return back()->with('error', 'Error al registrar pago: ' . $e->getMessage());
        }
    }

    public function payInstallment(Request $request)
    {
        \Log::info("=== PAY_INSTALLMENT: Petición recibida ===", $request->all());

        $request->validate([
            'installment_id' => 'required|exists:sale_installments,id',
            'payment_method' => 'required|string',
        ]);

        $installment = \Modules\Sales\Entities\SaleInstallment::find($request->installment_id);

        if (!$installment) {
            return back()->with('error', 'Cuota no encontrada.');
        }
        
        if ($installment->status == 1) {
            return back()->with('error', 'Esta cuota ya ha sido pagada.');
        }

        $amountToPay = floatval($installment->amount) - floatval($installment->paid_amount);
        \Log::info("PAY_INSTALLMENT: Cuota ID={$installment->id}, Sale ID={$installment->sale_id}, Monto a pagar={$amountToPay}");

        // Merge and delegate to storeAbono
        $request->merge([
            'abonable_id'        => $installment->sale_id,
            'abonable_type'      => 'Modules\Sales\Entities\Sale',
            'amount'             => $amountToPay,
            'installment_number' => $installment->installment_number,
        ]);

        return $this->storeAbono($request);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('credits::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('credits::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('credits::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function printReceipt($id)
    {
        $abono = \Modules\Credits\Entities\Abono::with('user', 'abonable')->findOrFail($id);
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('credits::pdf.receipt', compact('abono', 'settings'))
            ->setPaper('a5', 'landscape'); // A5 landscape for receipt is common
            
        return $pdf->stream('recibo_'.$abono->id.'.pdf');
    }
}
