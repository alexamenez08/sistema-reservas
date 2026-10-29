<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    protected $table = 'paquetes';
 
    protected $fillable = [
        'nombre',
        'descripcion',
        'capacidad',
        'precio_venta',
        'precio_persona_extra',
        'activo',
        'id_locacion',
    ];
 
    protected $casts = [
        'activo'      => 'boolean',
        'precio_venta'=> 'decimal:2',
        'precio_persona_extra' => 'decimal:2',
    ];
 
    public function locacion()
    {
        return $this->belongsTo(Locacion::class, 'id_locacion');
    }
 
    public function mobiliario()
    {
        return $this->belongsToMany(Mobiliario::class, 'inventario_paquete', 'id_paquete', 'id_articulo')
                    ->withPivot('cantidad');
    }
 
    public function platillos()
    {
        return $this->belongsToMany(CatalogoPlatillo::class, 'comida_paquete', 'id_paquete', 'id_comida');
    }
 
    // Servicios adicionales con precio fijo (montaje, DJ, coordinación...)
    public function servicios()
    {
        return $this->hasMany(ServicioPaquete::class, 'id_paquete');
    }
 
    public function reservaciones()
    {
        return $this->hasMany(Reservacion::class, 'paquete_id');
    }
}
