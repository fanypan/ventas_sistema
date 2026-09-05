<?php

namespace Modules\Sales\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Credits\Entities\Abono;
use Modules\Customers\Entities\Customer;
use Modules\Sales\Database\factories\SaleFactory;

class Sale extends Model
{
    use HasFactory;

    public const STATUS_VOIDED = 0;

    public const STATUS_PAID = 1;

    public const STATUS_CREDIT = 2;

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
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function abonos()
    {
        return $this->morphMany(Abono::class, 'abonable');
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isPaid(): bool
    {
        return (int) $this->status === self::STATUS_PAID;
    }

    public function isCredit(): bool
    {
        return (int) $this->status === self::STATUS_CREDIT;
    }

    public function isVoided(): bool
    {
        return (int) $this->status === self::STATUS_VOIDED;
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
    protected function notVoided(Builder $query): void
    {
        $query->where('status', '!=', self::STATUS_VOIDED);
    }

    #[Scope]
    protected function receivable(Builder $query): void
    {
        $query->where(function (Builder $inner) {
            $inner->where('payment_type', 'credito')
                ->orWhereIn('status', [self::STATUS_CREDIT, 3])
                ->orWhereHas('abonos');
        });
    }

    protected static function newFactory()
    {
        return SaleFactory::new();
    }
}
