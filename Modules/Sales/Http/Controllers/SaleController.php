<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customers\Entities\Customer;
use Modules\Financials\Entities\Caja;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Sales\Actions\VoidSale;
use Modules\Sales\Entities\Sale;

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
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        $query = Sale::with('customer', 'creator', 'installments')->latest();

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
        $products = Product::with('brand')->active()->get();
        $categories = Category::all();
        $cashOpen = Caja::openForUser();

        return view('sales::pos', compact('products', 'categories', 'cashOpen'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        $sale = Sale::with('customer', 'details.product', 'installments', 'abonos.user')->findOrFail($id);

        return view('sales::show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        if ($sale->isVoided()) {
            return redirect()->route('sales.show', $sale->id)
                ->with('error', 'No se puede editar una venta anulada.');
        }

        $customers = Customer::active()->orderBy('name')->get();

        return view('sales::edit', compact('sale', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        if ($sale->isVoided()) {
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
     *
     * @param  int  $id
     * @return Renderable
     */
    public function printTicket($id)
    {
        $sale = Sale::with('customer', 'details.product', 'creator')->findOrFail($id);
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView('sales::pdf.ticket', compact('sale', 'settings'))
            ->setPaper([0, 0, 226.77, 800], 'portrait'); // 80mm width roughly

        return $pdf->stream('ticket_'.$sale->id.'.pdf');
    }

    public function printInvoice($id)
    {
        $sale = Sale::with('customer', 'details.product', 'creator')->findOrFail($id);
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView('sales::pdf.invoice', compact('sale', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('factura_'.$sale->id.'.pdf');
    }

    public function void($id, VoidSale $voidSale)
    {
        $sale = Sale::findOrFail($id);
        $voidSale->execute($sale);

        return back()->with('success', 'Venta anulada. Se devolvió el stock.');
    }
}
