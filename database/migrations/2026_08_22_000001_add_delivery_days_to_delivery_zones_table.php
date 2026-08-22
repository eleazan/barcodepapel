<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Días de reparto de cada zona, en ISO-8601 (1 = lunes … 7 = domingo).
     *
     * Se deja a null en las zonas ya existentes: sin días marcados se reparte
     * cualquier día que la librería esté abierta, que es el comportamiento que
     * había hasta ahora.
     */
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->json('delivery_days')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn('delivery_days');
        });
    }
};
