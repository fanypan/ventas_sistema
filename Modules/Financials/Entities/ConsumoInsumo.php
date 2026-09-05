<?php

namespace Modules\Financials\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ConsumoInsumo extends Model
{
    protected $table = 'consumo_insumos';

    protected $fillable = [
        'insumo_id',
        'quantity',
        'user_id',
        'notes',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
