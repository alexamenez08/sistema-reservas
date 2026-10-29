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
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->string('actividad', 180);
            $table->text('descripcion')->nullable();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedBigInteger('evento_id');
            $table->unsignedBigInteger('responsable');
            $table->foreign('evento_id')
                  ->references('id')
                  ->on('eventos')
                  ->onDelete('cascade');
            $table->foreign('responsable')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
