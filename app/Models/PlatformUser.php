<?php

namespace App\Models;

use App\Support\PlatformAccess;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class PlatformUser extends Authenticatable
{
    use HasRoles;
    use Notifiable;

    public const ROLE_ADMIN = PlatformAccess::ROLE_ADMIN;

    public const ROLE_STAFF = PlatformAccess::ROLE_STAFF;

    protected string $guard_name = PlatformAccess::GUARD;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function syncLegacyRoleColumn(): void
    {
        $name = $this->hasRole(self::ROLE_ADMIN)
            ? self::ROLE_ADMIN
            : ($this->roles()->pluck('name')->first() ?: self::ROLE_STAFF);

        if ($this->role !== $name) {
            $this->forceFill(['role' => $name])->saveQuietly();
        }
    }

    public static function adminCount(): int
    {
        return self::role(self::ROLE_ADMIN)->count();
    }
}
