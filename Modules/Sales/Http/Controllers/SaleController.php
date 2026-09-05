<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCrud;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Customers\Entities\Customer;
use Modules\Financials\Entities\Caja;
use Modules\Products\Entities\Category;
use Modules\Products\Entities\Product;
use Modules\Sales\Actions\VoidSale;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Http\Requests\UpdateSaleRequest;

class SaleController extends Controller
{
    use AuthorizesCrud;

    public function __construct()
    {
        $this->authorizeCrud('sale', ['printTicket', 'printInvoice'], ['pos']);
        $this->middleware('permission:void sale')->only(['void']);
    }

    public function index(Request $request): View
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

    public function pos(): View
    {
        $products = Product::with('brand')->active()->get();
        $categories = Category::all();
        $cashOpen = Caja::openForUser();

        return view('sales::pos', compact('products', 'categories', 'cashOpen'));
    }

    public function create(): View
    {
        return view('sales::create');
    }

    public function store(Request $request): void
    {
        //
    }

    public function show($id): View
    {
        $sale = Sale::with('customer', 'details.product', 'installments', 'abonos.user')->findOrFail($id);

        return view('sales::show', compact('sale'));
    }

    public function edit($id): View|RedirectResponse
    {
        $sale = Sale::with('customer')->findOrFail($id);

        if ($sale->isVoided()) {
            return redirect()->route('sales.show', $sale->id)
                ->with('error', 'No se puede editar una venta anulada.');
        }

        $customers = Customer::active()->orderBy('name')->get();

        return view('sales::edit', compact('sale', 'customers'));
    }

    public function update(UpdateSaleRequest $request, $id): RedirectResponse
    {
        $sale = Sale::findOrFail($id);

        if ($sale->isVoided()) {
            return back()->with('error', 'No se puede editar una venta anulada.');
        }

        $data = $request->validated();

        $sale->created_at = $data['fecha'];
        $sale->customer_id = $data['customer_id'];
        $sale->payment_type = $data['payment_type'];
        $sale->status = (int) $data['status'];
        $sale->save();

        return redirect()->route('sales.index')->with('success', 'Venta actualizada correctamente.');
    }

    public function printTicket($id): Response
    {
        $sale = Sale::with('customer', 'details.product', 'creator')->findOrFail($id);
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView('sales::pdf.ticket', compact('sale', 'settings'))
            ->setPaper([0, 0, 226.77, 800], 'portrait'); // 80mm width roughly

        return $pdf->stream('ticket_'.$sale->id.'.pdf');
    }

    public function printInvoice($id): Response
    {
        $sale = Sale::with('customer', 'details.product', 'creator')->findOrFail($id);
        $settings = Setting::all()->pluck('value', 'key');

        $pdf = Pdf::loadView('sales::pdf.invoice', compact('sale', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('factura_'.$sale->id.'.pdf');
    }

    public function void($id, VoidSale $voidSale): RedirectResponse
    {
        $sale = Sale::findOrFail($id);
        $voidSale->execute($sale);

        return back()->with('success', 'Venta anulada. Se devolvió el stock.');
    }
}
