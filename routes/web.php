<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AnticipoController;
use App\Http\Controllers\AsignacionStaffController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CatalogoPlatilloController;
use App\Http\Controllers\CategoriaMobiliarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionNegocioController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\LocacionController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\MobiliarioController;
use App\Http\Controllers\PaqueteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservacionController;
use App\Http\Controllers\UserController;
use App\Models\CategoriaMobiliario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
| Matriz de acceso (RD.1):
|
|   Sección                                   Administrador  Coordinador  Staff
|   Dashboard y reportes                           X
|   Usuarios y perfil de la empresa                X
|   Clientes, catálogos, paquetes, reservaciones,
|   cotizaciones, mapa, gestión de eventos
|   y asignación de staff                          X              X
|   Calendario y consulta de eventos               X              X           X
*/
Route::middleware('auth')->group(function () {
 
    // ---------- Todos los roles ----------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
 
    // El administrador ve el dashboard; coordinador y staff entran directo al calendario
    Route::get('/dashboard', function (Request $request) {
        /** @var \App\Models\User $usuario */
        $usuario = $request->user();
 
        return $usuario->hasRole('administrador')
            ? view('dashboard')
            : redirect()->route('calendario.index');
    })->name('dashboard');
 
    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/calendario/eventos', [CalendarioController::class, 'eventos'])->name('calendario.eventos');
 
    // ---------- Solo administrador ----------
    Route::middleware('rol:administrador')->group(function () {
        Route::resource('usuarios', UserController::class);
 
        Route::get('/empresa', [ConfiguracionNegocioController::class, 'edit'])->name('empresa.edit');
        Route::put('/empresa', [ConfiguracionNegocioController::class, 'update'])->name('empresa.update');
    });
 
    // ---------- Administrador y coordinador ----------
    Route::middleware('rol:administrador,coordinador')->group(function () {
        Route::get('/mapa', [MapaController::class, 'index'])->name('mapa.index');
 
        Route::resource('clientes', ClienteController::class);
 
        Route::resource('locaciones', LocacionController::class)
            ->parameters(['locaciones' => 'locacion']);
        Route::resource('categorias-mobiliario', CategoriaMobiliarioController::class);
        Route::resource('mobiliario', MobiliarioController::class);
        Route::resource('catalogo-platillos', CatalogoPlatilloController::class);
        Route::resource('paquetes', PaqueteController::class);
 
        Route::resource('reservaciones', ReservacionController::class)
            ->parameters(['reservaciones' => 'reservacion']);
        Route::get('/cotizador/calcular', [ReservacionController::class, 'calcular'])->name('cotizador.calcular');
        Route::get('/reservaciones/{reservacion}/cotizacion', [CotizacionController::class, 'descargar'])->name('reservaciones.cotizacion');
        Route::post('reservaciones/{reservacion}/anticipos', [AnticipoController::class, 'store'])->name('anticipos.store');
        Route::delete('anticipos/{anticipo}', [AnticipoController::class, 'destroy'])->name('anticipos.destroy');
 
        // Eventos: todo menos consultar (index y show están más abajo, para todos los roles).
        // Va ANTES para que /eventos/create no la capture la ruta show.
        Route::resource('eventos', EventoController::class)
            ->except(['index', 'show'])
            ->parameters(['eventos' => 'evento']);
        Route::post('eventos/{evento}/actividades', [ActividadController::class, 'store'])->name('actividades.store');
        Route::put('actividades/{actividad}', [ActividadController::class, 'update'])->name('actividades.update');
        Route::delete('actividades/{actividad}', [ActividadController::class, 'destroy'])->name('actividades.destroy');
        Route::post('eventos/{evento}/staff', [AsignacionStaffController::class, 'store'])->name('staff.store');
        Route::delete('staff/{asignacionStaff}', [AsignacionStaffController::class, 'destroy'])->name('staff.destroy');
    });
 
    // ---------- Todos los roles: consulta de eventos ----------
    Route::resource('eventos', EventoController::class)
        ->only(['index', 'show'])
        ->parameters(['eventos' => 'evento']);
});

require __DIR__.'/auth.php';
