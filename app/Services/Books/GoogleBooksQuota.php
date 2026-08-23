<?php

declare(strict_types=1);

namespace App\Services\Books;

use Illuminate\Support\Facades\Cache;

/**
 * Contador de la cuota diaria de Google Books.
 *
 * La API limita las peticiones por proyecto y día (1.000 por defecto). Sin un
 * contador propio los jobs se comen la cuota en minutos y el resto muere a
 * base de 429; con él sabemos cuántos libros se pueden pedir hoy antes de
 * encolar nada, y el panel lo puede enseñar.
 *
 * El contador vive en caché, no en BD: es un dato del día que se puede perder
 * sin consecuencias (en el peor caso, unos cuantos 429 que el job ya maneja).
 */
class GoogleBooksQuota
{
    public function used(): int
    {
        return (int) Cache::get($this->key(), 0);
    }

    public function limit(): int
    {
        return (int) config('services.google_books.daily_quota', 1000);
    }

    public function remaining(): int
    {
        return max(0, $this->limit() - $this->used());
    }

    public function exhausted(): bool
    {
        return $this->remaining() === 0;
    }

    /** Registra una petición consumida. Devuelve el total del día. */
    public function hit(): int
    {
        $key = $this->key();

        // add() crea la clave con TTL solo si no existía; increment() la sube
        // sin tocar el vencimiento, así el contador caduca a medianoche.
        Cache::add($key, 0, now()->endOfDay());

        return (int) Cache::increment($key);
    }

    /** Segundos hasta que la cuota se renueva. */
    public function secondsUntilReset(): int
    {
        // Carbon 3 devuelve float en los diff: hay que castear.
        return max(60, (int) now()->diffInSeconds(now()->endOfDay()));
    }

    private function key(): string
    {
        return 'google_books:quota:'.now()->toDateString();
    }
}
