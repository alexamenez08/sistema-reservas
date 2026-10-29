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
        Schema::create('asignacion_staff', function (Blueprint $table) {
            $table->id();
            $table->string('rol_asignado', 100);
            $table->decimal('pago_evento', 8, 2);
            $table->unsignedBigInteger('evento_id');
            $table->unsignedBigInteger('empleado_id');
            $table->foreign('evento_id')
                  ->references('id')
                  ->on('eventos')
                  ->onDelete('cascade');
            $table->foreign('empleado_id')
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
        Schema::dropIfExists('asignacion_staff');
    }
};
