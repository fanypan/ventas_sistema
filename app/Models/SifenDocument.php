<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SifenDocument extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'sale_id',
        'document_type',
        'cdc',
        'status',
        'partner_reference',
        'payload',
        'response',
        'error_message',
        'issued_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'issued_at' => 'datetime',
    ];

    #[Scope]
    protected function countsTowardQuota(Builder $query): void
    {
        $query->whereIn('status', [self::STATUS_SENT, self::STATUS_APPROVED, self::STATUS_PENDING]);
    }
}
