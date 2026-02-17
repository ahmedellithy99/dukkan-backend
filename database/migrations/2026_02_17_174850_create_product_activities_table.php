<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('activity_type', ['view', 'whatsapp_click', 'location_click', 'sms_click', 'favorite']);
            $table->string('ip_address', 45)->nullable(); // Support IPv4 and IPv6
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('product_id');
            $table->index('activity_type');
            $table->index('created_at');
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_activities');
    }
};
