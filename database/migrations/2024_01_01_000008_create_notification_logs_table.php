<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 30);         // email, whatsapp, telegram, sms...
            $table->string('recipient');            // email address, phone number, etc.
            $table->string('subject')->nullable();
            $table->text('body');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('event');                // status_changed, manual_resend, etc.
            $table->json('metadata')->nullable();   // extra data per channel
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'channel']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
