<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Herramientas de PostgreSQL para el respaldo y la restauración (FN19)
    |--------------------------------------------------------------------------
    | Si pg_dump / psql no están en el PATH del sistema (lo normal en Windows),
    | indica la ruta completa en el archivo .env, con diagonales normales:
    |
    |   PG_DUMP_PATH="C:/Program Files/PostgreSQL/16/bin/pg_dump.exe"
    |   PSQL_PATH="C:/Program Files/PostgreSQL/16/bin/psql.exe"
    */
    'pg_dump' => env('PG_DUMP_PATH', 'pg_dump'),
    'psql'    => env('PSQL_PATH', 'psql'),

    // Segundos máximos que puede tardar un respaldo o una restauración
    'timeout' => (int) env('RESPALDO_TIMEOUT', 300),
];