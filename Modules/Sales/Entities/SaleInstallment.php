<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInstallment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_PAID = 1;

    public const STATUS_CANCELLED = 2;

    protected $fillable = [
        'sale_id',
        'installment_number',
        'amount',
        'paid_amount', // Added
        'due_date',
        'status',
        'paid_at',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function isPending(): bool
    {
        return (int) $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return (int) $this->status === self::STATUS_PAID;
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    #[Scope]
    protected function paid(Builder $query): void
    {
        $query->where('status', self::STATUS_PAID);
    }

    #[Scope]
    protected function cancelled(Builder $query): void
    {
        $query->where('status', self::STATUS_CANCELLED);
    }
}
