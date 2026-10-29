<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /* Precio de venta por persona de cada platillo (lo que se le cobra al cliente).
     * costo_preparacion se queda como costo INTERNO y ya no se usa en la cotización. */
    public function up(): void
    {
        Schema::table('catalogo_platillos', function (Blueprint $table) {
            //
            $table->decimal('precio_venta', 12, 2)->nullable()->after('costo_preparacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogo_platillos', function (Blueprint $table) {
            //
            $table->dropColumn('precio_venta');
        });
    }
};
