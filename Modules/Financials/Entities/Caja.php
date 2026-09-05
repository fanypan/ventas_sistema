<?php

namespace Modules\Financials\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Credits\Entities\Abono;
use Modules\Financials\Database\factories\CajaFactory;
use Modules\Sales\Entities\Sale;

class Caja extends Model
{
    use HasFactory;

    public const STATUS_CLOSED = 0;

    public const STATUS_OPEN = 1;

    protected $fillable = [
        'user_id',
        'opening_amount',
        'closing_amount',
        'opened_at',
        'closed_at',
        'status',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function openForUser(?int $userId = null): ?self
    {
        $userId ??= auth()->id();
        if (! $userId) {
            return null;
        }

        return static::query()
            ->open()
            ->where('user_id', $userId)
            ->first();
    }

    public function isOpen(): bool
    {
        return (int) $this->status === self::STATUS_OPEN;
    }

    public function paidSalesTotal(): float
    {
        $sales = $this->relationLoaded('sales')
            ? $this->sales->where('status', Sale::STATUS_PAID)
            : $this->sales()->paid()->get();

        return (float) $sales->sum('total');
    }

    #[Scope]
    protected function open(Builder $query): void
    {
        $query->where('status', self::STATUS_OPEN);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cash_id');
    }

    public function abonos()
    {
        return $this->hasMany(Abono::class, 'cash_id');
    }

    public function expenses()
    {
        return $this->hasMany(Gasto::class, 'cash_id');
    }

    protected static function newFactory()
    {
        return CajaFactory::new();
    }
}
