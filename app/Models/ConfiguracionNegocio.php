<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ConfiguracionNegocio extends Model
{
    //
        protected $table = 'configuracion_negocio';
 
    protected $fillable = [
        'nombre',
        'logo',
        'telefono',
        'correo',
        'direccion',
    ];
 
    /**
     * Devuelve el perfil de la empresa (la única fila). Si todavía no se ha
     * capturado, regresa una instancia vacía SIN guardar para que el sistema
     * funcione con el nombre por defecto (APP_NAME).
     */
    public static function actual(): self
    {
        return static::query()->first() ?? new static();
    }
 
    /** Nombre a mostrar: el capturado o, si no hay, APP_NAME. */
    public function getNombreMostrarAttribute(): string
    {
        return $this->nombre ?: config('app.name');
    }
 
    /** URL pública del logo (requiere `php artisan storage:link`). */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo && Storage::disk('public')->exists($this->logo)
            ? asset('storage/' . $this->logo)
            : null;
    }
 
    /** Logo como data URI, para incrustarlo en el PDF (dompdf). */
    public function logoBase64(): ?string
    {
        if (! $this->logo || ! Storage::disk('public')->exists($this->logo)) {
            return null;
        }
 
        $ext  = strtolower(pathinfo($this->logo, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
 
        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($this->logo));
    }
}
