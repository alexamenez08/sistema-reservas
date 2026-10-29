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
        Schema::table('reservaciones', function (Blueprint $table) {
            //
            $table->decimal('descuento', 10, 2)->default(0)->after('costo_total');
            $table->string('motivo_descuento', 150)->nullable()->after('descuento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservaciones', function (Blueprint $table) {
            //
            $table->dropColumn(['descuento', 'motivo_descuento']);
        });
    }
};
