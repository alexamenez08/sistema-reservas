<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locacion extends Model
{
    //
    protected $primaryKey = 'id';
 
    public function getRouteKeyName(): string
    {
        return 'id';
    }
 
    use SoftDeletes;
 
    protected $table = 'locaciones';
 
    protected $fillable = [
        'nombre',
        'descripcion',
        'capacidad',
        'precio_base',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'entidad_feder',
        'codigo_postal',
        'latitud',
        'longitud',
        'activo',
    ];
 
    protected $casts = [
        'activo' => 'boolean',
        'precio_base' => 'decimal:2',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7', // antes decía "longititud" (typo) y el cast no se aplicaba
    ];
 
    /**
     * Dirección en una sola línea: "Calle 123, Int. 4, Colonia, CP 62000, Morelos"
     */
    protected function direccion(): Attribute
    {
        return Attribute::make(
            get: function () {
                $calle = trim($this->calle . ' ' . $this->numero_exterior);
                if ($this->numero_interior) {
                    $calle .= ', Int. ' . $this->numero_interior;
                }
 
                return collect([
                    $calle,
                    $this->colonia,
                    $this->codigo_postal ? 'CP ' . $this->codigo_postal : null,
                    $this->entidad_feder,
                ])->filter()->implode(', ');
            }
        );
    }
 
    //relación con reservaciones
    public function reservaciones() 
    {
        return $this->hasMany(Reservacion::class, 'locacion_id');        
    }
 
    //relación con paquetes
    public function paquetes()
    {
        return $this->hasMany(Paquete::class, 'id_locacion');        
    }
}