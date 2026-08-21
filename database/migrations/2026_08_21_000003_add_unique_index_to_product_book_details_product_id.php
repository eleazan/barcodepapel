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
        DB::statement('
            DELETE d1 FROM product_book_details d1
            INNER JOIN product_book_details d2
                ON d1.product_id = d2.product_id
               AND d1.id > d2.id
        ');

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
