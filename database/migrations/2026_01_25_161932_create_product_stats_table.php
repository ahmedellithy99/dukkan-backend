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
        Schema::create('product_stats', function (Blueprint $table) {
            $table->foreignId('product_id')->primary()->constrained('products')->onDelete('cascade');
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('whatsapp_clicks')->default(0);
            $table->unsignedBigInteger('sms_clicks')->default(0);
            $table->unsignedBigInteger('favorites_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('views_count');
            $table->index('favorites_count');
            $table->index('last_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stats');
    }
};
