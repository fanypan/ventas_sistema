<?php

namespace Modules\Suppliers\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
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
        return \Modules\Suppliers\Database\factories\SupplierFactory::new();
    }
}
