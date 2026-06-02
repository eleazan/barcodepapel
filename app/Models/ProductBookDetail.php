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
        'google_books_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'paginas'                => 'integer',
            'anio_publicacion'       => 'integer',
            'google_books_synced_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
