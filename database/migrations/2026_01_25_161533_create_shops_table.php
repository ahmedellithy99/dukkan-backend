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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            $table->string('phone_number', 20);
            $table->string('whatsapp_number', 20);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Core discovery + vendor listing
            $table->index(['location_id', 'is_active']);
            $table->index(['owner_id', 'is_active']);

            // Fulltext search (MySQL only)
            if (DB::connection()->getDriverName() === 'mysql') {
                $table->fullText(['name', 'description']);
            }

            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
