<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('verial_id')->nullable()->unique()->after('sku');
            $table->string('barcode', 30)->nullable()->after('verial_id');
            $table->tinyInteger('tipo_articulo')->default(1)->after('barcode');
            $table->decimal('iva_percent', 5, 2)->default(4.00)->after('price');
            $table->date('fecha_disponibilidad')->nullable()->after('iva_percent');
            $table->date('fecha_inicio_venta')->nullable()->after('fecha_disponibilidad');
            $table->dateTime('fecha_inactivo')->nullable()->after('fecha_inicio_venta');
            $table->string('nexo', 100)->nullable()->after('fecha_inactivo');
            $table->decimal('peso', 10, 3)->nullable()->after('nexo');
            $table->unsignedBigInteger('verial_fabricante_id')->nullable()->after('peso');
            $table->timestamp('verial_synced_at')->nullable()->after('verial_fabricante_id');

            $table->foreign('verial_fabricante_id')
                ->references('id')
                ->on('verial_fabricantes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['verial_fabricante_id']);
            $table->dropColumn([
                'verial_id',
                'barcode',
                'tipo_articulo',
                'iva_percent',
                'fecha_disponibilidad',
                'fecha_inicio_venta',
                'fecha_inactivo',
                'nexo',
                'peso',
                'verial_fabricante_id',
                'verial_synced_at',
            ]);
        });
    }
};
