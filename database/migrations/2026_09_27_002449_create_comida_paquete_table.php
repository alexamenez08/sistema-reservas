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
        Schema::create('comida_paquete', function (Blueprint $table) {
            $table->unsignedBigInteger('id_paquete');
            $table->unsignedBigInteger('id_comida');

            $table->primary(['id_paquete', 'id_comida']);

            $table->foreign('id_paquete')
                  ->references('id')
                  ->on('paquetes')
                  ->onDelete('cascade');

            $table->foreign('id_comida')
                  ->references('id')
                  ->on('catalogo_platillos')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comida_paquete');
    }
};
