<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerialFabricante extends Model
{
    protected $table = 'verial_fabricantes';

    protected $fillable = [
        'verial_id',
        'nombre',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'verial_fabricante_id');
    }
}
