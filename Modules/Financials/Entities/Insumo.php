<?php

namespace Modules\Financials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function expenses()
    {
        return $this->hasMany(Gasto::class, 'insumo_id');
    }
}
