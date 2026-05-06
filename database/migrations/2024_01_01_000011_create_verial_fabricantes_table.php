<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verial_fabricantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('verial_id')->unique();
            $table->string('nombre', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verial_fabricantes');
    }
};
