<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoPlatillo extends Model
{
    //
    protected $table = 'catalogo_platillos';

    protected $fillable = [
        'nombre',
        'categoria',
        'descripcion',
        'costo_preparacion',
        'precio_venta',
        'imagen',
    ];

    protected $casts = [
        'costo_preparacion' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    public function paquetes(){
        return $this->belongsToMany(Paquete::class, 'comida_paquete', 'id_comida', 'id_paquete');
    }
}
