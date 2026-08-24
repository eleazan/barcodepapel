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
        'cover_source',
        'cover_fetched_at',
        'cover_attempts',
        'cover_attempted_at',
    ];

    /** Intentos sin portada tras los que el libro se descarta. */
    public const MAX_COVER_ATTEMPTS = 3;

    protected function casts(): array
    {
        return [
            'paginas'                => 'integer',
            'anio_publicacion'       => 'integer',
            'google_books_synced_at' => 'datetime',
            'cover_fetched_at'       => 'datetime',
            'cover_attempts'         => 'integer',
            'cover_attempted_at'     => 'datetime',
        ];
    }

    public function hasCover(): bool
    {
        return $this->cover_fetched_at !== null;
    }

    public function coverDiscarded(): bool
    {
        return $this->cover_fetched_at === null
            && $this->cover_attempts >= self::MAX_COVER_ATTEMPTS;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
