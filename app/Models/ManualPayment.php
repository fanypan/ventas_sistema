<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualPayment extends Model
{
    public const METHOD_TRANSFER = 'transferencia';

    public const METHOD_CASH = 'efectivo';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'platform_user_id',
        'amount',
        'method',
        'reference',
        'paid_at',
        'notes',
        'attachment_path',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(PlatformUser::class, 'platform_user_id');
    }

    public function methodLabel(): string
    {
        return [
            self::METHOD_TRANSFER => 'Transferencia',
            self::METHOD_CASH => 'Efectivo',
        ][$this->method] ?? $this->method;
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return route('platform.payments.attachment', [$this->tenant_id, $this]);
    }
}
