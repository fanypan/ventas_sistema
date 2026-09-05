<?php

namespace Modules\Products\Entities;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Database\factories\CategoryFactory;

class Category extends Model
{
    use HasActiveStatus;
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
        return CategoryFactory::new();
    }
}
