<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngfintech_transactions', function (Blueprint $table) {
            $table->id();

            // Who made this transaction
            $table->nullableMorphs('transactable'); // polymorphic — links to any model (User, Business etc)

            // What category and provider
            $table->string('category');          // payment, airtime, bills, identity, banking
            $table->string('driver');            // paystack, flutterwave, vtpass etc
            $table->string('action');            // initializeTransaction, purchaseAirtime etc

            // Transaction identity
            $table->string('reference')->unique()->nullable();
            $table->string('provider_reference')->nullable(); // provider's own reference

            // Financial details
            $table->unsignedBigInteger('amount')->default(0); // always in kobo
            $table->string('currency', 3)->default('NGN');

            // Status tracking
            $table->enum('status', [
                'pending',
                'success',
                'failed',
                'reversed',
            ])->default('pending');

            // Full request and response — critical for debugging
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            // Extra metadata — flexible for any driver
            $table->json('metadata')->nullable();

            // Error tracking
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();

            // IP and user agent for audit
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for fast querying
            $table->index(['category', 'driver']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngfintech_transactions');
    }
};