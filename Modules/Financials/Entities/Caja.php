<?php

namespace Modules\Financials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Caja extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opening_amount',
        'closing_amount',
        'opened_at',
        'closed_at',
        'status',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function sales()
    {
        return $this->hasMany(\Modules\Sales\Entities\Sale::class, 'cash_id');
    }

    public function abonos()
    {
        return $this->hasMany(\Modules\Credits\Entities\Abono::class, 'cash_id');
    }

    public function expenses()
    {
        return $this->hasMany(Gasto::class, 'cash_id');
    }
    
    protected static function newFactory()
    {
        return \Modules\Financials\Database\factories\CajaFactory::new();
    }
}
