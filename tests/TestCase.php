<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Los tests no dependen de los assets compilados: sin esto, cualquier
        // vista con @vite revienta si no se ha ejecutado antes `npm run build`.
        $this->withoutVite();
    }
}
