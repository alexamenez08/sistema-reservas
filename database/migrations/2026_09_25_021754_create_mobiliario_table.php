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
        Schema::create('mobiliario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->integer('stock_total');
            $table->integer('stock_disponible');
            $table->decimal('precio_renta_unitario', 12, 2);
            $table->decimal('costo_reposicion', 12, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('id_categoriamob')
                ->references('id')
                ->on('categorias_mobiliario')
                ->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobiliario');
    }
};
