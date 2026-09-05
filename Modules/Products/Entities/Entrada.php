<?php

namespace Modules\Products\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Database\factories\EntradaFactory;

class Entrada extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return EntradaFactory::new();
    }
}
