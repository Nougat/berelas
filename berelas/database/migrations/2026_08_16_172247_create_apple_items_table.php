<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apple_items', function (Blueprint $table) {
            
        $table->uuid('id')->primary();
            $table->timestamps();

            $table->foreignUuid('inventory_item_id')
                ->unique()
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('release_year')->nullable();
            $table->unsignedSmallInteger('generation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apple_items');
    }
};
