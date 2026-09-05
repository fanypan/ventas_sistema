<?php

namespace Modules\Customers\Entities;

use App\Models\Concerns\HasActiveStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Customers\Database\factories\CustomerFactory;

class Customer extends Model
{
    use HasActiveStatus;
    use HasFactory;

    protected $fillable = [
        'nit',
        'name',
        'phone',
        'address',
        'user_id',
        'status',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function newFactory()
    {
        return CustomerFactory::new();
    }
}
