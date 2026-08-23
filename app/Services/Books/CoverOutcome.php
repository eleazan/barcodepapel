<?php

declare(strict_types=1);

namespace App\Services\Books;

/**
 * Resultado de intentar conseguir la portada y la ficha de un libro.
 */
enum CoverOutcome: string
{
    /** Portada descargada y guardada. */
    case Obtenida = 'obtenida';

    /** El producto ya tenía portada local: no se ha tocado. */
    case YaTenia = 'ya_tenia';

    /** Ninguna de las fuentes tiene portada para este ISBN. */
    case SinPortada = 'sin_portada';

    /** El producto no tiene código de barras con el que buscar. */
    case SinIsbn = 'sin_isbn';

    /** La fuente ha respondido 429 o la cuota diaria está agotada. */
    case LimiteAlcanzado = 'limite_alcanzado';

    /** Fallo de red o respuesta inesperada. */
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Obtenida        => 'Portada obtenida',
            self::YaTenia         => 'Ya tenía portada',
            self::SinPortada      => 'Sin portada en ninguna fuente',
            self::SinIsbn         => 'Sin ISBN',
            self::LimiteAlcanzado => 'Límite de la API alcanzado',
            self::Error           => 'Error al consultar la fuente',
        };
    }

    /** Si conviene volver a encolar el job en lugar de darlo por hecho. */
    public function shouldRetry(): bool
    {
        return $this === self::LimiteAlcanzado;
    }
}
