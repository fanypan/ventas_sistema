<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\Concerns\AuthorizesCrud;

class SaleController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('sale', ['printTicket', 'printInvoice'], ['pos']);
        $this->middleware('permission:void sale')->only(['void']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $query = \Modules\Sales\Entities\Sale::with('customer', 'creator', 'installments')->latest();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->paginate(15);
        return view('sales::index', compact('sales'));
    }

    public function pos()
    {
        $products = \Modules\Products\Entities\Product::with('brand')->where('status', 1)->get();
        $categories = \Modules\Products\Entities\Category::all();
        $cashOpen = \Modules\Financials\Entities\Caja::where('status', 1)->first();
        
        return view('sales::pos', compact('products', 'categories', 'cashOpen'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
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
        $sale = \Modules\Sales\Entities\Sale::with('customer', 'details.product', 'installments', 'abonos.user')->findOrFail($id);
        return view('sales::show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $sale = \Modules\Sales\Entities\Sale::with('customer')->findOrFail($id);

        if ($sale->status == 0) {
            return redirect()->route('sales.show', $sale->id)
                ->with('error', 'No se puede editar una venta anulada.');
        }

        $customers = \Modules\Customers\Entities\Customer::where('status', 1)->orderBy('name')->get();

        return view('sales::edit', compact('sale', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $sale = \Modules\Sales\Entities\Sale::findOrFail($id);

        if ($sale->status == 0) {
            return back()->with('error', 'No se puede editar una venta anulada.');
        }

        $request->validate([
            'fecha' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:efectivo,qr,tarjeta,transferencia,credito',
            'status' => 'required|in:1,2,3',
        ]);

        $sale->created_at = $request->fecha;
        $sale->customer_id = $request->customer_id;
        $sale->payment_type = $request->payment_type;
        $sale->status = (int) $request->status;
        $sale->save();

        return redirect()->route('sales.index')->with('success', 'Venta actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function printTicket($id)
    {
        $sale = \Modules\Sales\Entities\Sale::with('customer', 'details.product', 'creator')->findOrFail($id);
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales::pdf.ticket', compact('sale', 'settings'))
            ->setPaper([0, 0, 226.77, 800], 'portrait'); // 80mm width roughly
            
        return $pdf->stream('ticket_'.$sale->id.'.pdf');
    }

    public function printInvoice($id)
    {
        $sale = \Modules\Sales\Entities\Sale::with('customer', 'details.product', 'creator')->findOrFail($id);
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales::pdf.invoice', compact('sale', 'settings'))
            ->setPaper('a4', 'portrait');
            
        return $pdf->stream('factura_'.$sale->id.'.pdf');
    }

    public function void($id)
    {
        $sale = \Modules\Sales\Entities\Sale::with('details.product')->findOrFail($id);

        if ($sale->status == 0) {
            return back()->with('error', 'Esta venta ya ha sido anulada.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Devolver Stock (Replicando stored procedure anular_venta)
            foreach ($sale->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                }
                // Marcar detalle como anulado si existe campo status en sale_details
                // $detail->update(['status' => 0]); 
            }

            // 2. Anular Cuotas si es crédito
            if ($sale->payment_type === 'credito') {
                $sale->installments()->update(['status' => 2]); // 2 = Anulada en nuestro sistema
            }

            // 3. Cambiar estado de la venta
            $sale->status = 0; // 0 = Anulada
            $sale->save();

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Venta anulada correctamente. Stock restablecido.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error al anular venta: ' . $e->getMessage());
        }
    }
}
