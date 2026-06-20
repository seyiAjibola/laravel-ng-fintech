<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngfintech_webhooks', function (Blueprint $table) {
            $table->id();

            // Which provider sent this webhook
            $table->string('driver');            // paystack, flutterwave etc
            $table->string('event');             // charge.success, transfer.success etc

            // Link to transaction if we can match it
            $table->foreignId('transaction_id')
                  ->nullable()
                  ->constrained('ngfintech_transactions')
                  ->nullOnDelete();

            // Raw webhook data — never discard this
            $table->json('payload');

            // Signature verification
            $table->string('signature')->nullable();
            $table->boolean('verified')->default(false);

            // Processing status
            $table->enum('status', [
                'received',
                'processed',
                'failed',
                'ignored',
            ])->default('received');

            $table->text('error_message')->nullable();

            // Source IP of webhook
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['driver', 'event']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngfintech_webhooks');
    }
};