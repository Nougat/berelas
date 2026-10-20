<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();

            // Kunde
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();

            // Gerät und Annahme
            $table->string('device')->nullable();
            $table->date('received_at');
            $table->string('received_by')->nullable();
            $table->date('purchase_date')->nullable();

            // Reparatur
            $table->text('description')->nullable();
            $table->string('condition')->nullable();
            $table->text('password')->nullable();
            $table->boolean('power_supply')->default(false);
            $table->boolean('damage_fixed')->nullable();
            $table->boolean('customer_notified')->default(false);
            $table->decimal('cost', 10, 2)->nullable();

            // Auftragsverwaltung
            $table->string('status')->default('open');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};