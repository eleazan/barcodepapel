<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('verial_pedido_id')->nullable()->after('notes');
            $table->string('verial_referencia', 50)->nullable()->after('verial_pedido_id');
            $table->string('verial_estado', 30)->nullable()->after('verial_referencia');
            $table->timestamp('verial_enviado_at')->nullable()->after('verial_estado');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'verial_pedido_id',
                'verial_referencia',
                'verial_estado',
                'verial_enviado_at',
            ]);
        });
    }
};
