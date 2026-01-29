<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('subcategory_id')->constrained('subcategories')->restrictOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            $table->decimal('price', 10, 2)->nullable();

            $table->enum('discount_type', ['percent', 'amount'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();

            $table->integer('stock_quantity')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Core listing performance
            $table->unique(['shop_id', 'slug']);

            $table->index(['shop_id', 'is_active', 'created_at']);
            $table->index(['subcategory_id', 'is_active', 'created_at']);

            // Fulltext search (MySQL only)
            if (DB::connection()->getDriverName() === 'mysql') {
                $table->fullText(['name', 'description']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
