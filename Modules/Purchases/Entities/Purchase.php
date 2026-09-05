<?php

namespace Modules\Purchases\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Credits\Entities\Abono;
use Modules\Suppliers\Entities\Supplier;

class Purchase extends Model
{
    use HasFactory;

    public const STATUS_VOIDED = 0;

    public const STATUS_PAID = 1;

    public const STATUS_CREDIT = 2;

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
        return $this->morphMany(Abono::class, 'abonable');
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

    #[Scope]
    protected function paid(Builder $query): void
    {
        $query->where('status', self::STATUS_PAID);
    }

    #[Scope]
    protected function credit(Builder $query): void
    {
        $query->where('status', self::STATUS_CREDIT);
    }

    #[Scope]
    protected function payable(Builder $query): void
    {
        $query->where(function (Builder $inner) {
            $inner->credit()->orWhereHas('abonos');
        });
    }
}
