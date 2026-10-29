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
        Schema::create('inventario_paquete', function (Blueprint $table) {
            $table->unsignedBigInteger('id_paquete');
            $table->unsignedBigInteger('id_articulo');
            $table->integer('cantidad');

            $table->primary(['id_paquete', 'id_articulo']);

            $table->foreign('id_paquete')
                  ->references('id')
                  ->on('paquetes')
                  ->onDelete('cascade');

            $table->foreign('id_articulo')
                  ->references('id')
                  ->on('mobiliario')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_paquete');
    }
};
