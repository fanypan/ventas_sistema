<?php

namespace App\Models;

use App\Support\TenantDatabaseName;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use InvalidArgumentException;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_GRACE = 'grace';

    public const STATUS_READONLY = 'readonly';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_CANCELLED = 'cancelled';

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'ruc',
            'status',
            'plan_id',
            'admin_name',
            'admin_email',
            'admin_password_hash',
            'brand_color',
            'provisioned_at',
            'admin_password_set_at',
        ];
    }

    protected $casts = [
        'provisioned_at' => 'datetime',
        'admin_password_set_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tenant) {
            if ($tenant->slug && ! TenantDatabaseName::slugIsValid($tenant->slug)) {
                throw new InvalidArgumentException(
                    'El slug solo puede tener letras minúsculas y números, sin guion ni guion bajo.'
                );
            }
        });
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ManualPayment::class);
    }

    /**
     * @return Collection<int, $this>
     */
    public static function catalogSources(?self $except = null)
    {
        return static::query()
            ->when($except, fn ($query) => $query->whereKeyNot($except->getTenantKey()))
            ->whereNotNull('provisioned_at')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function primaryDomain(): string
    {
        return $this->domains->first()?->domain
            ?? $this->slug.'.'.config('saas.tenant_base_domain');
    }

    public function url(): string
    {
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
        $port = parse_url(config('app.url'), PHP_URL_PORT);
        $host = $this->primaryDomain();

        return $scheme.'://'.$host.($port ? ':'.$port : '');
    }

    public function adminNeedsPassword(): bool
    {
        return $this->provisioned_at !== null && $this->admin_password_set_at === null;
    }

    public function isOperational(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_GRACE], true);
    }

    public function isReadOnly(): bool
    {
        return $this->status === self::STATUS_READONLY;
    }

    public function isBlocked(): bool
    {
        return in_array($this->status, [self::STATUS_SUSPENDED, self::STATUS_CANCELLED, self::STATUS_PENDING], true);
    }

    public function statusLabel(): string
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_GRACE => 'En gracia',
            self::STATUS_READONLY => 'Solo lectura',
            self::STATUS_SUSPENDED => 'Pausado',
            self::STATUS_CANCELLED => 'Baja',
        ][$this->status] ?? $this->status;
    }

    public function statusTone(): string
    {
        return [
            self::STATUS_PENDING => 'info',
            self::STATUS_ACTIVE => 'ok',
            self::STATUS_GRACE => 'warn',
            self::STATUS_READONLY => 'info',
            self::STATUS_SUSPENDED => 'bad',
            self::STATUS_CANCELLED => 'neutral',
        ][$this->status] ?? 'neutral';
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    #[Scope]
    protected function grace(Builder $query): void
    {
        $query->where('status', self::STATUS_GRACE);
    }

    #[Scope]
    protected function restricted(Builder $query): void
    {
        $query->whereIn('status', [self::STATUS_SUSPENDED, self::STATUS_READONLY]);
    }

    #[Scope]
    protected function billable(Builder $query): void
    {
        $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_PENDING]);
    }
}
