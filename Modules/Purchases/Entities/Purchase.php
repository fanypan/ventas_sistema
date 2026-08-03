<?php

namespace Modules\Purchases\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Modules\Suppliers\Entities\Supplier;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'total',
        'status',
    ];
    
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function abonos()
    {
        return $this->morphMany(\Modules\Credits\Entities\Abono::class, 'abonable');
    }

    public function total_paid()
    {
        return $this->abonos()->sum('amount');
    }

    public function pending_balance()
    {
        return $this->total - $this->total_paid();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
