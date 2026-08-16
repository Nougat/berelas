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
        Schema::create('inventory_items', function (Blueprint $table) {
            
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->string('shelf')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('cpu')->nullable();
            $table->integer('ram')->nullable();
            $table->integer('ssd')->nullable();
            $table->string('gpu')->nullable();
            $table->integer('amount')->nullable();
            $table->string('condition')->nullable();
            $table->string('specials')->nullable();
            $table->string('layout')->nullable();
            $table->integer('price')->nullable();
            $table->string('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
