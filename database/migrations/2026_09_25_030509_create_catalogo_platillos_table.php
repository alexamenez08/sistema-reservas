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
        Schema::create('catalogo_platillos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('categoria', ['entrada', 'plato fuerte', 'postre', 'bebida', 'menu infantil']);
            $table->text('descripcion')->nullable();
            $table->decimal('costo_preparacion', 12, 2)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_platillos');
    }
};
