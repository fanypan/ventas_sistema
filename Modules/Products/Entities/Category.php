<?php

namespace Modules\Products\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    protected static function newFactory()
    {
        return \Modules\Products\Database\factories\CategoryFactory::new();
    }
}
