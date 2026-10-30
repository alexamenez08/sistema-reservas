<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * FN19. Respaldo y restauración de la base de datos (solo administrador).
 *
 *  - Respaldo:     pg_dump en formato SQL; el archivo queda en storage/app/private/respaldos y se puede descargar.
 *  - Restauración: psql en UNA sola transacción (si algo falla, no se cambia nada) y antes se crea
 *                  automáticamente un respaldo "previo" por si hay que deshacer.
 *  - No se incluyen tablas temporales (sesiones, caché, colas) para no cerrar la sesión de nadie.
 *  - Solo respalda la base de datos; las imágenes y el logo (carpeta storage) se copian aparte.
 */
class RespaldoController extends Controller
{
    private const DIRECTORIO = 'respaldos';

    /** Nombre válido de un archivo de respaldo (evita rutas manipuladas al descargar o borrar). */
    private const PATRON = '/^respaldo_\d{4}-\d{2}-\d{2}_\d{6}(_previo)?\.sql$/';

    /**
     * Tablas temporales que no se respaldan. Sus secuencias también deben excluirse: si no, el .sql
     * intenta borrar jobs_id_seq mientras la tabla jobs sigue existiendo y la restauración falla.
     */
    private const TABLAS_EXCLUIDAS = [
        'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
        'jobs_id_seq', 'failed_jobs_id_seq',
    ];

    public function index(): View
    {
        $disco = Storage::disk('local');

        $respaldos = collect($disco->files(self::DIRECTORIO))
            ->filter(fn ($ruta) => preg_match(self::PATRON, basename($ruta)))
            ->map(fn ($ruta) => [
                'nombre' => basename($ruta),
                'tamano' => $this->formatoTamano($disco->size($ruta)),
                'fecha'  => Carbon::createFromTimestamp($disco->lastModified($ruta), config('app.timezone')),
                'previo' => str_ends_with($ruta, '_previo.sql'),
            ])
            ->sortByDesc('fecha')
            ->values();

        return view('respaldos.index', compact('respaldos'));
    }

    /** Genera un respaldo nuevo. */
    public function store(Request $request): RedirectResponse
    {
        set_time_limit(0);
        $nombre = 'respaldo_' . now()->format('Y-m-d_His') . '.sql';

        try {
            $this->generarRespaldo($nombre);
        } catch (RuntimeException $e) {
            return redirect()->route('respaldos.index')
                ->with('error', 'No se pudo generar el respaldo. ' . $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Error inesperado al generar el respaldo: ' . $e->getMessage());

            return redirect()->route('respaldos.index')
                ->with('error', 'No se pudo generar el respaldo por un error inesperado. Revisa storage/logs/laravel.log.');
        }

        Log::info('Respaldo generado', ['archivo' => $nombre, 'usuario_id' => $request->user()?->id]);

        // FN20: aquí se creará la notificación del respaldo (fecha y hora).

        return redirect()->route('respaldos.index')
            ->with('success', "Respaldo generado correctamente: {$nombre}");
    }

    /** Descarga un respaldo guardado. */
    public function descargar(string $archivo): BinaryFileResponse
    {
        abort_unless(preg_match(self::PATRON, $archivo) && is_file($this->rutaAbsoluta($archivo)), 404);

        return response()->download($this->rutaAbsoluta($archivo), $archivo);
    }

    /** Elimina un respaldo guardado. */
    public function destroy(string $archivo): RedirectResponse
    {
        abort_unless(preg_match(self::PATRON, $archivo), 404);

        Storage::disk('local')->delete($this->ruta($archivo));

        return redirect()->route('respaldos.index')
            ->with('success', 'Respaldo eliminado correctamente.');
    }

    /** Restaura la base de datos desde un archivo .sql generado por este módulo. */
    public function restaurar(Request $request): RedirectResponse
    {
        set_time_limit(0);

        $request->validate([
            'archivo' => [
                'bail', 'required', 'file', 'max:512000',
                function ($atributo, $archivo, $fallo) {
                    if (strtolower($archivo->getClientOriginalExtension()) !== 'sql') {
                        $fallo('El archivo debe tener extensión .sql.');
                        return;
                    }
                    $inicio = (string) file_get_contents($archivo->getRealPath(), false, null, 0, 4096);
                    if (! str_contains($inicio, 'PostgreSQL database dump')) {
                        $fallo('El archivo no es un respaldo válido generado por el sistema.');
                    }
                },
            ],
            'confirmacion' => ['required', 'in:RESTAURAR'],
        ], [
            'archivo.required'      => 'Selecciona el archivo de respaldo (.sql).',
            'archivo.file'          => 'No se pudo leer el archivo. Revisa el límite de tamaño de subida de PHP.',
            'archivo.max'           => 'El archivo es demasiado grande (máximo 500 MB).',
            'confirmacion.required' => 'Escribe RESTAURAR para confirmar.',
            'confirmacion.in'       => 'Escribe exactamente la palabra RESTAURAR para confirmar.',
        ]);

        // 1) Respaldo previo de seguridad: si no se puede crear, no se restaura nada.
        $previo = 'respaldo_' . now()->format('Y-m-d_His') . '_previo.sql';
        try {
            $this->generarRespaldo($previo);
        } catch (RuntimeException $e) {
            return redirect()->route('respaldos.index')
                ->with('error', 'No se restauró nada: no se pudo crear el respaldo previo de seguridad. ' . $e->getMessage());
        }

        // 2) Restauración en una sola transacción (todo o nada)
        try {
            $c = $this->conexion();
            $this->ejecutar([
                config('respaldos.psql'),
                '--host=' . $c['host'],
                '--port=' . $c['port'],
                '--username=' . $c['username'],
                '--dbname=' . $c['database'],
                '--single-transaction',
                '--set=ON_ERROR_STOP=1',
                '--quiet',
                '--file=' . $request->file('archivo')->getRealPath(),
            ], $c['password'], 'psql');
        } catch (RuntimeException $e) {
            return redirect()->route('respaldos.index')
                ->with('error', 'No se pudo restaurar el respaldo; la base de datos no se modificó. ' . $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Error inesperado al restaurar: ' . $e->getMessage());

            return redirect()->route('respaldos.index')
                ->with('error', 'No se pudo restaurar el respaldo por un error inesperado. Revisa storage/logs/laravel.log.');
        }

        // Los roles y permisos pudieron cambiar: se limpia su caché
        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (Throwable $e) {
            // no es crítico
        }

        Log::info('Base de datos restaurada', ['usuario_id' => $request->user()?->id, 'respaldo_previo' => $previo]);

        return redirect()->route('respaldos.index')
            ->with('success', "Base de datos restaurada correctamente. Antes se guardó una copia de seguridad: {$previo}. "
                . 'Si tu usuario no existía en ese respaldo, tendrás que iniciar sesión de nuevo.');
    }

    /* ===================================================================
     *  Utilidades
     * =================================================================== */

    private function generarRespaldo(string $nombre): void
    {
        $c     = $this->conexion();
        $disco = Storage::disk('local');
        $disco->makeDirectory(self::DIRECTORIO);
        $destino = $this->rutaAbsoluta($nombre);

        $comando = [
            config('respaldos.pg_dump'),
            '--host=' . $c['host'],
            '--port=' . $c['port'],
            '--username=' . $c['username'],
            '--dbname=' . $c['database'],
            '--format=plain',
            '--no-owner',
            '--no-privileges',
            '--clean',
            '--if-exists',
            '--file=' . $destino,
        ];
        foreach (self::TABLAS_EXCLUIDAS as $tabla) {
            $comando[] = '--exclude-table=' . $tabla;
        }

        try {
            $this->ejecutar($comando, $c['password'], 'pg_dump');
        } catch (RuntimeException $e) {
            @unlink($destino); // no dejar archivos a medias
            throw $e;
        }

        if (! is_file($destino) || filesize($destino) === 0) {
            @unlink($destino);
            throw new RuntimeException('El archivo de respaldo quedó vacío.');
        }
    }

    /** Datos de conexión de PostgreSQL tomados de la configuración de Laravel (.env). */
    private function conexion(): array
    {
        if (config('database.default') !== 'pgsql') {
            throw new RuntimeException('El respaldo solo está disponible cuando la base de datos es PostgreSQL.');
        }

        $c = config('database.connections.pgsql');

        return [
            'host'     => $c['host'],
            'port'     => $c['port'],
            'database' => $c['database'],
            'username' => $c['username'],
            'password' => (string) ($c['password'] ?? ''),
        ];
    }

    /** Ejecuta pg_dump o psql. La contraseña viaja en una variable de entorno, no en la línea de comandos. */
    private function ejecutar(array $comando, string $password, string $herramienta): void
    {
        // En Windows, `php artisan serve` no hereda variables como SystemRoot. Sin ellas, pg_dump 18
        // falla con "no se pudo generar la llave restrict" (no puede obtener números aleatorios).
        $entorno = ['PGPASSWORD' => $password, 'PGCLIENTENCODING' => 'UTF8'];
        foreach (['SystemRoot', 'windir', 'SystemDrive', 'TEMP', 'TMP', 'PATH', 'ComSpec'] as $var) {
            $valor = getenv($var);
            if ($valor !== false && $valor !== '') {
                $entorno[$var] = $valor;
            }
        }
        if (PHP_OS_FAMILY === 'Windows') {
            $entorno['SystemRoot'] ??= 'C:\\Windows';
            $entorno['windir']     ??= $entorno['SystemRoot'];
        }

        $proceso = new Process($comando, null, $entorno);
        $proceso->setTimeout(config('respaldos.timeout'));

        try {
            $proceso->run();
        } catch (ProcessTimedOutException $e) {
            throw new RuntimeException("{$herramienta} tardó demasiado y se canceló. Aumenta RESPALDO_TIMEOUT en el .env.");
        }

        if ($proceso->isSuccessful()) {
            return;
        }

        // La consola de Windows devuelve el texto en CP850/1252; si no se convierte a UTF-8,
        // el mensaje flash puede romper la respuesta o el guardado de la sesión.
        $error = trim($proceso->getErrorOutput() . ' ' . $proceso->getOutput());
        if (! mb_check_encoding($error, 'UTF-8')) {
            $error = mb_convert_encoding($error, 'UTF-8', 'CP850');
        }
        Log::warning("{$herramienta} falló: " . $error);

        throw new RuntimeException($this->traducirError($error, $herramienta));
    }

    private function traducirError(string $error, string $herramienta): string
    {
        return match (true) {
            (bool) preg_match('/no se reconoce|not recognized|not found|no such file|cannot find|no se puede encontrar/i', $error)
                => "No se encontró la herramienta {$herramienta}. Indica su ruta completa en el archivo .env (PG_DUMP_PATH y PSQL_PATH).",
            (bool) preg_match('/password authentication failed|autentificaci[oó]n/i', $error)
                => 'PostgreSQL rechazó el usuario o la contraseña. Revisa DB_USERNAME y DB_PASSWORD en el .env.',
            (bool) preg_match('/server version mismatch|versi[oó]n del servidor/i', $error)
                => "La versión de {$herramienta} es más antigua que la del servidor PostgreSQL. Usa la herramienta de la misma versión del servidor.",
            default => "{$herramienta} reportó un error: " . Str::limit($error, 300),
        };
    }

    /** Ruta relativa dentro del disco "local" (para exists, delete, files...). */
    private function ruta(string $archivo): string
    {
        return self::DIRECTORIO . '/' . $archivo;
    }

    /** Ruta completa en el disco duro (para pg_dump y para descargar). */
    private function rutaAbsoluta(string $archivo): string
    {
        return rtrim((string) config('filesystems.disks.local.root'), '/\\')
            . DIRECTORY_SEPARATOR . self::DIRECTORIO
            . DIRECTORY_SEPARATOR . $archivo;
    }

    private function formatoTamano(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1) . ' MB'
            : number_format(max(1, $bytes) / 1024, 1) . ' KB';
    }
}