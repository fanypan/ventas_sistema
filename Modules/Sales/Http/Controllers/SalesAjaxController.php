<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Rules\RucParaguay;
use App\Support\RucParaguay as RucParaguaySupport;
use Modules\Products\Entities\Product;
use Modules\Customers\Entities\Customer;
use Modules\Sales\Entities\TemporaryDetail;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleDetail;
use Modules\Sales\Entities\SaleInstallment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesAjaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create sale')->except(['storeCustomer']);
        $this->middleware('permission:create customer')->only(['storeCustomer']);
    }

    public function searchProduct(Request $request)
    {
        $term = $request->term;
        $product = Product::where('status', 1)
            ->where(function($query) use ($term) {
                $query->where('code', $term)
                    ->orWhere('description', 'LIKE', "%$term%");
            })->first();

        if ($product) {
            return response()->json($product);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function getCart()
    {
        $details = TemporaryDetail::with('product')
            ->where('user_token', $this->cartToken())
            ->get();

        $sub_total = $details->sum(function($d) {
            return $d->quantity * $d->price;
        });

        return response()->json([
            'details' => $details,
            'sub_total' => $sub_total
        ]);
    }

    public function addToCart(Request $request)
    {
        $token = $this->cartToken();
        $product_id = $request->product_id;
        $quantity = $request->quantity;

        $product = Product::find($product_id);
        if (!$product) return response()->json(['error' => 'Product not found'], 404);

        $tempDetail = TemporaryDetail::where('user_token', $token)
            ->where('product_id', $product_id)
            ->first();

        if ($tempDetail) {
            $diff = $quantity;
            if ($product->stock < $diff) {
                return response()->json(['error' => 'Stock insuficiente. Disponible: ' . $product->stock], 422);
            }
            $tempDetail->quantity += $quantity;
            $tempDetail->save();
            
            $product->decrement('stock', $diff);
        } else {
            if ($product->stock < $quantity) {
                return response()->json(['error' => 'Stock insuficiente. Disponible: ' . $product->stock], 422);
            }
            TemporaryDetail::create([
                'user_token' => $token,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $product->price,
                'cost' => $product->cost,
            ]);
            $product->decrement('stock', $quantity);
        }

        // SistemaVenta reservaría stock aquí, pero en Laravel es mejor validar stock disponible
        // antes de procesar la venta final para evitar inconsistencias si el usuario abandona el carrito.
        // Sin embargo, para "aplicar todo", restaremos stock o validaremos seriamente.
        // Por ahora, aseguramos que el costo sea correcto.

        return $this->getCart();
    }

    public function removeFromCart(Request $request)
    {
        $detail = $this->cartLine($request->id);
        if ($detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->increment('stock', $detail->quantity);
            }
            $detail->delete();
        }

        return $this->getCart();
    }

    public function clearCart()
    {
        $details = TemporaryDetail::where('user_token', $this->cartToken())->get();
        foreach ($details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->increment('stock', $detail->quantity);
            }
            $detail->delete();
        }
        return response()->json(['success' => true]);
    }

    public function searchCustomer(Request $request)
    {
        $term = $request->term;
        $customer = Customer::where('status', 1)
            ->where(function($query) use ($term) {
                $query->where('nit', $term)
                    ->orWhere('name', 'LIKE', "%$term%");
            })->first();

        if ($customer) {
            return response()->json($customer);
        }
        return response()->json(['error' => 'Not found'], 404);
    }

    public function listCustomers()
    {
        $customers = Customer::where('status', 1)->orderBy('name')->get();
        return response()->json($customers);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nit'  => ['required', new RucParaguay(), 'unique:customers,nit'],
        ]);

        $customer = Customer::create([
            'name'    => $request->name,
            'nit'     => RucParaguaySupport::format($request->nit),
            'phone'   => $request->phone,
            'address' => $request->address,
            'user_id' => auth()->id(),
            'status'  => 1
        ]);

        return response()->json($customer);
    }

    public function updateCartItem(Request $request)
    {
        $id = $request->id;
        $quantity = (int)$request->quantity;
        $discount = parse_currency($request->discount ?? 0);
        $interest = parse_currency($request->interest ?? 0);

        $tempDetail = $this->cartLine($id);
        if ($tempDetail) {
            $product = Product::find($tempDetail->product_id);
            if ($product) {
                $diff = $quantity - $tempDetail->quantity; // positive means we need more stock
                if ($diff > 0) {
                    if ($product->stock < $diff) {
                        return response()->json(['error' => 'Stock insuficiente para el ajuste'], 422);
                    }
                    $product->decrement('stock', $diff);
                } else if ($diff < 0) {
                    $product->increment('stock', abs($diff));
                }
            }

            $tempDetail->quantity = $quantity;
            $tempDetail->discount = $discount;
            $tempDetail->interest_amount = $interest;
            $tempDetail->save();
        }

        return $this->getCart();
    }

    public function processSale(Request $request)
    {
        $token = $this->cartToken();
        $details = TemporaryDetail::where('user_token', $token)->get();

        if ($details->isEmpty()) {
            return response()->json(['error' => 'El carrito está vacío'], 422);
        }

        // 1. Validar que la caja esté abierta
        $caja = \Modules\Financials\Entities\Caja::where('status', 1)->first();
        if (!$caja) {
            return response()->json(['error' => 'No hay una caja abierta. Debe abrir la caja para realizar ventas.'], 422);
        }

        $payment_type = $request->payment_type ?? 'efectivo';
        $payment_with = parse_currency($request->payment_with);
        $discountPercent = parse_currency($request->discount);

        DB::beginTransaction();
        try {
            $subtotal = $details->sum(function($d) {
                return $d->quantity * $d->price;
            });

            // Apply discount
            $discountAmount = round($subtotal * $discountPercent / 100);
            $total = $subtotal - $discountAmount;

            // Determine status: 1=Pagada, 2=Crédito
            $status = 1;
            if ($payment_type === 'credito') {
                $status = 2;
                $payment_with = 0;
            }

            // For non-cash payments (except credit), payment_with = total
            if ($payment_type !== 'efectivo' && $payment_type !== 'credito') {
                $payment_with = $total;
            }

            // Validation: cash must cover total
            if ($payment_type === 'efectivo' && $payment_with < $total) {
                return response()->json(['error' => 'El monto pagado no cubre el total'], 422);
            }

            $interestType = $request->interest_type ?? 'amount'; // percent or amount
            $interestVal  = parse_currency($request->interest_value);
            
            if ($interestType === 'percent') {
                $interestAmount = round($total * $interestVal / 100);
            } else {
                $interestAmount = $interestVal;
            }

            $installmentsCount = (int) ($request->installments ?? 1);

            $sale = Sale::create([
                'user_id'      => auth()->id(),
                'customer_id'  => $request->customer_id ?? 1,
                'total'        => $total + $interestAmount,
                'discount'     => $discountAmount,
                'interest_amount' => $interestAmount,
                'installments_count' => $installmentsCount,
                'payment_type' => $payment_type,
                'payment_with' => $payment_with,
                'change'       => $payment_type === 'efectivo' ? max(0, $payment_with - ($total + $interestAmount)) : 0,
                'reference_number' => $request->reference_number,
                'payment_note' => $request->payment_note,
                'status'       => $status,
                'cash_id'      => $caja->id, // FIXED: Using the active caja found above
            ]);

            // Generate Installments if Credit
            if ($payment_type === 'credito') {
                $customInstallmentAmount = parse_currency($request->installment_amount);
                $frequency = $request->frequency ?? 'mensual';
                
                $totalToDistribute = $total + $interestAmount;
                $installmentAmount = ($customInstallmentAmount > 0) ? $customInstallmentAmount : round($totalToDistribute / $installmentsCount);
                
                for ($i = 1; $i <= $installmentsCount; $i++) {
                    $dueDate = Carbon::now();
                    if ($frequency === 'semanal') {
                        $dueDate->addWeeks($i);
                    } elseif ($frequency === 'quincenal') {
                        $dueDate->addDays($i * 15);
                    } else { // mensual
                        $dueDate->addMonths($i);
                    }

                    SaleInstallment::create([
                        'sale_id' => $sale->id,
                        'installment_number' => $i,
                        'amount' => $installmentAmount,
                        'due_date' => $dueDate->format('Y-m-d'),
                        'status' => 0,
                    ]);
                }
            }

            foreach ($details as $detail) {
                SaleDetail::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $detail->product_id,
                    'quantity'   => $detail->quantity,
                    'price'      => $detail->price,
                    'cost'       => $detail->cost, // FIXED: Added cost
                    'discount'   => $detail->discount,
                    'interest_amount' => $detail->interest_amount,
                ]);

                // Stock ya fue descontado al agregar al carrito (estilo SistemaVenta)
                // $product = Product::find($detail->product_id);
                // if ($product) {
                //     $product->stock -= $detail->quantity;
                //     $product->save();
                // }
            }

            TemporaryDetail::where('user_token', $token)->delete();

            DB::commit();

            try {
                app(\App\Services\Sifen\SifenIssuer::class)->issueForSale($sale->fresh());
            } catch (\Throwable $e) {
                report($e);
            }

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'change'  => $payment_type === 'efectivo' ? max(0, $payment_with - $total) : 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json(['error' => 'No se pudo cerrar la venta. Intentá de nuevo.'], 500);
        }
    }

    private function cartToken(): string
    {
        return md5((string) auth()->id());
    }

    private function cartLine(mixed $id): ?TemporaryDetail
    {
        if ($id === null || $id === '') {
            return null;
        }

        return TemporaryDetail::where('user_token', $this->cartToken())
            ->where('id', $id)
            ->first();
    }
}
