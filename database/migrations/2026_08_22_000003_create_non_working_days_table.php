<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Días en que la librería no reparte: festivos y cierres por vacaciones.
     *
     * Un solo día se guarda con `starts_on` y `ends_on` iguales; un cierre por
     * vacaciones ocupa el rango completo. Los festivos de fecha fija se marcan
     * como recurrentes y valen para todos los años.
     */
    public function up(): void
    {
        Schema::create('non_working_days', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('recurs_annually')->default(false);
            $table->timestamps();

            $table->index('starts_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_working_days');
    }
};
