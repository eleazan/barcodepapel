<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// El ->unique() de la migración original quedó encadenado al foreign key en
// lugar de a la columna, así que el índice nunca llegó a crearse y la tabla
// admite varias fichas por producto.
return new class extends Migration
{
    public function up(): void
    {
        // De cada product_id se conserva la ficha más antigua. El DELETE con JOIN
        // solo lo entiende MySQL, así que se resuelve con una subconsulta derivada
        // (MySQL no admite leer la tabla que borra sin ese nivel intermedio) para
        // que la migración también corra en SQLite, la conexión de los tests.
        $fichasAConservar = DB::table('product_book_details')
            ->selectRaw('MIN(id) as id')
            ->groupBy('product_id');

        DB::table('product_book_details')
            ->whereNotIn('id', fn ($query) => $query->fromSub($fichasAConservar, 'conservar')->select('id'))
            ->delete();

        Schema::table('product_book_details', function (Blueprint $table) {
            $table->unique('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_book_details', function (Blueprint $table) {
            $table->dropUnique(['product_id']);
        });
    }
};
