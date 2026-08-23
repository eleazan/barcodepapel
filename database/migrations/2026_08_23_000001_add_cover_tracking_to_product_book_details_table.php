<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_book_details', function (Blueprint $table) {
            // De dónde salió la portada: google_books u openlibrary.
            $table->string('cover_source', 30)->nullable()->after('google_books_synced_at');
            // Cuándo se consiguió. Con valor, el libro ya no vuelve a la cola.
            $table->timestamp('cover_fetched_at')->nullable()->after('cover_source');
            // Intentos sin portada. Al llegar al tope, el libro se descarta y
            // tampoco vuelve a la cola hasta que se reprocese a mano.
            $table->unsignedTinyInteger('cover_attempts')->default(0)->after('cover_fetched_at');
            $table->timestamp('cover_attempted_at')->nullable()->after('cover_attempts');

            $table->index(['cover_fetched_at', 'cover_attempts'], 'pbd_cover_pendientes_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_book_details', function (Blueprint $table) {
            $table->dropIndex('pbd_cover_pendientes_index');
            $table->dropColumn([
                'cover_source',
                'cover_fetched_at',
                'cover_attempts',
                'cover_attempted_at',
            ]);
        });
    }
};
