<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las notificaciones dejan de colgar solo del pedido.
     *
     * Los avisos de cuenta —verificación, contraseña, bienvenida— no tienen
     * pedido detrás, así que `order_id` pasa a ser opcional y se añade
     * `user_id` para poder listarlas también en la ficha del cliente.
     */
    public function up(): void
    {
        Schema::getConnection()->getDriverName() === 'sqlite'
            ? $this->recrearTabla()
            : $this->alterarTabla();

        // Los avisos ya registrados se enganchan al cliente de su pedido.
        DB::table('notification_logs')
            ->whereNull('user_id')
            ->whereNotNull('order_id')
            ->update([
                'user_id' => DB::raw('(select user_id from orders where orders.id = notification_logs.order_id)'),
            ]);
    }

    /**
     * Se quita la columna; el índice se va con ella (MySQL no deja soltarlo
     * antes, porque lo necesita la clave foránea).
     *
     * `order_id` se queda opcional: revertirlo exigiría borrar los avisos de
     * cuenta, que no tienen pedido al que volver.
     */
    public function down(): void
    {
        // Tres pasos, en este orden y por separado. MySQL no suelta el índice
        // mientras lo necesite la clave foránea, y si se borra la columna sin
        // haberlo soltado antes, el índice sobrevive reducido a `created_at`
        // con el mismo nombre y la migración ya no se puede volver a aplicar.
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }

    /**
     * MySQL: basta con añadir la columna y aflojar `order_id`.
     */
    private function alterarTabla(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('order_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['user_id', 'created_at']);
        });

        DB::statement('ALTER TABLE notification_logs MODIFY order_id BIGINT UNSIGNED NULL');
    }

    /**
     * SQLite no sabe convertir una columna en nullable, así que se recrea la
     * tabla entera y se copian las filas.
     */
    private function recrearTabla(): void
    {
        // Al renombrar, los índices se llevan su nombre: hay que soltarlos antes
        // o chocarán con los de la tabla nueva.
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'channel']);
            $table->dropIndex(['status']);
        });

        Schema::rename('notification_logs', 'notification_logs_old');

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 30);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('event');
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'channel']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });

        $columnas = 'id, order_id, channel, recipient, subject, body, status, error_message, event, metadata, sent_at, created_at, updated_at';

        DB::statement("INSERT INTO notification_logs ({$columnas}) SELECT {$columnas} FROM notification_logs_old");

        Schema::drop('notification_logs_old');
    }
};
