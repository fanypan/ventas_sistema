<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'cash_id',
        'total',
        'discount',
        'interest_amount',
        'installments_count',
        'payment_with',
        'change',
        'payment_type',
        'reference_number',
        'payment_note',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(\Modules\Customers\Entities\Customer::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function abonos()
    {
        return $this->morphMany(\Modules\Credits\Entities\Abono::class, 'abonable');
    }

    public function installments()
    {
        return $this->hasMany(SaleInstallment::class);
    }

    public function total_paid()
    {
        return $this->abonos()->sum('amount');
    }

    public function pending_balance()
    {
        $paid = $this->total_paid();
        $pending = $this->total - $paid;
        \Log::info("Venta #{$this->id}: Total={$this->total}, Pagado={$paid}, Pendiente={$pending}");
        return $pending;
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
    
    protected static function newFactory()
    {
        return \Modules\Sales\Database\factories\SaleFactory::new();
    }
}
