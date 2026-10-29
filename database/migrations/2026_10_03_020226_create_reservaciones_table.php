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
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->id();
            $table->date('dia_evento');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('contador_invitados');
            $table->decimal('costo_total', 10, 2);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);
            $table->enum('status', ['pendiente', 'confirmada', 'en curso', 'finalizada', 'cancelada'])->default('pendiente');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('locacion_id');
            $table->unsignedBigInteger('paquete_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('locacion_id')->references('id')->on('locaciones')->onDelete('restrict');
            $table->foreign('paquete_id')->references('id')->on('paquetes')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservaciones');
    }
};
