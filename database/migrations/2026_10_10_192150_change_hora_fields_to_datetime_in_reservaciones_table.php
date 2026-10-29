<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE reservaciones ADD COLUMN hora_inicio_new TIMESTAMP(0) WITHOUT TIME ZONE');
        DB::statement('ALTER TABLE reservaciones ADD COLUMN hora_fin_new TIMESTAMP(0) WITHOUT TIME ZONE');
        DB::statement("UPDATE reservaciones SET hora_inicio_new = (dia_evento::date + hora_inicio::time)::timestamp");
        DB::statement("UPDATE reservaciones SET hora_fin_new = (dia_evento::date + hora_fin::time)::timestamp");
        DB::statement('ALTER TABLE reservaciones DROP COLUMN hora_inicio');
        DB::statement('ALTER TABLE reservaciones DROP COLUMN hora_fin');
        DB::statement('ALTER TABLE reservaciones RENAME COLUMN hora_inicio_new TO hora_inicio');
        DB::statement('ALTER TABLE reservaciones RENAME COLUMN hora_fin_new TO hora_fin');
        DB::statement('ALTER TABLE reservaciones ALTER COLUMN hora_inicio SET NOT NULL');
        DB::statement('ALTER TABLE reservaciones ALTER COLUMN hora_fin SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE reservaciones ADD COLUMN hora_inicio_old TIME');
        DB::statement('ALTER TABLE reservaciones ADD COLUMN hora_fin_old TIME');
        DB::statement('UPDATE reservaciones SET hora_inicio_old = hora_inicio::time');
        DB::statement('UPDATE reservaciones SET hora_fin_old = hora_fin::time');
        DB::statement('ALTER TABLE reservaciones DROP COLUMN hora_inicio');
        DB::statement('ALTER TABLE reservaciones DROP COLUMN hora_fin');
        DB::statement('ALTER TABLE reservaciones RENAME COLUMN hora_inicio_old TO hora_inicio');
        DB::statement('ALTER TABLE reservaciones RENAME COLUMN hora_fin_old TO hora_fin');
    }
};
