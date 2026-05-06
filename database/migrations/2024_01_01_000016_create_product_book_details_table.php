<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_book_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('isbn', 20)->nullable();
            $table->string('subtitulo', 255)->nullable();
            $table->string('autores', 500)->nullable();
            $table->string('editorial', 255)->nullable();
            $table->string('coleccion', 255)->nullable();
            $table->unsignedSmallInteger('paginas')->nullable();
            $table->string('edicion', 50)->nullable();
            $table->year('anio_publicacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_book_details');
    }
};
