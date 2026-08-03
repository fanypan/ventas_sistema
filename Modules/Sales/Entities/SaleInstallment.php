<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'installment_number',
        'amount',
        'paid_amount', // Added
        'due_date',
        'status',
        'paid_at',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
