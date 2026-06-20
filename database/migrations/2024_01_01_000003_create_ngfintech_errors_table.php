<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngfintech_errors', function (Blueprint $table) {
            $table->id();

            // Where the error came from
            $table->string('category');          // payment, airtime etc
            $table->string('driver');            // paystack, vtpass etc
            $table->string('action');            // which method failed

            // Link to transaction if one existed
            $table->foreignId('transaction_id')
                  ->nullable()
                  ->constrained('ngfintech_transactions')
                  ->nullOnDelete();

            // Error detail
            $table->string('error_code')->nullable();
            $table->text('error_message');
            $table->longText('stack_trace')->nullable();

            // What was sent when it failed
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            // Context
            $table->string('ip_address', 45)->nullable();
            $table->string('environment')->default(app()->environment());

            $table->timestamps();

            $table->index(['category', 'driver']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngfintech_errors');
    }
};