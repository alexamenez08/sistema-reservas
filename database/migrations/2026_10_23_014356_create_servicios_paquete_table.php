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
        Schema::create('servicios_paquete', function (Blueprint $table) {
            $table->id();
             $table->foreignId('id_paquete')->constrained('paquetes')->cascadeOnDelete();
            $table->string('concepto', 120);
            $table->decimal('precio', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios_paquete');
    }
};
