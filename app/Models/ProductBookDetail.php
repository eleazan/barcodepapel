<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBookDetail extends Model
{
    protected $table = 'product_book_details';

    protected $fillable = [
        'product_id',
        'isbn',
        'subtitulo',
        'autores',
        'editorial',
        'coleccion',
        'paginas',
        'edicion',
        'anio_publicacion',
    ];

    protected function casts(): array
    {
        return [
            'paginas'          => 'integer',
            'anio_publicacion' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
