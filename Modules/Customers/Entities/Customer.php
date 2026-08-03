<?php

namespace Modules\Customers\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
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
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
    
    protected static function newFactory()
    {
        return \Modules\Customers\Database\factories\CustomerFactory::new();
    }
}
