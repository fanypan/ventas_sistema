<?php

namespace Modules\Financials\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(User::class);
    }

    public function expenses()
    {
        return $this->hasMany(Gasto::class, 'insumo_id');
    }

    public function consumptions()
    {
        return $this->hasMany(ConsumoInsumo::class);
    }
}
