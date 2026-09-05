<?php

namespace Modules\Suppliers\Entities;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Suppliers\Database\factories\SupplierFactory;

class Supplier extends Model
{
    use HasActiveStatus;
    use HasFactory;

    protected $fillable = [
        'nit',
        'name',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected static function newFactory()
    {
        return SupplierFactory::new();
    }
}
