<?php

namespace Modules\Financials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gasto extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'date',
        'user_id',
        'cash_id',
        'type',
        'insumo_id',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    protected static function newFactory()
    {
        return \Modules\Financials\Database\factories\GastoFactory::new();
    }
}
