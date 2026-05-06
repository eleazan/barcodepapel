<?php

declare(strict_types=1);

namespace App\Services\Verial;

readonly class SyncResult
{
    public function __construct(
        public readonly int $processed,
        public readonly int $created,
        public readonly int $updated,
        public readonly int $errors,
        public readonly array $errorMessages = [],
    ) {}

    public function isOk(): bool
    {
        return $this->errors === 0;
    }

    public function summary(): string
    {
        return sprintf(
            'Procesados: %d | Creados: %d | Actualizados: %d | Errores: %d',
            $this->processed,
            $this->created,
            $this->updated,
            $this->errors,
        );
    }
}
