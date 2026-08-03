<?php

namespace Modules\Credits\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Abono extends Model
{
    use HasFactory;

    protected $fillable = [
        'abonable_id',
        'abonable_type',
        'amount',
        'payment_method',
        'payment_date',
        'reference',
        'note',
        'installment_number',
        'received_amount',
        'user_id',
        'cash_id',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function abonable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
