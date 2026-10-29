<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobiliario extends Model
{
    //
    protected $table = 'mobiliario';

    protected $fillable = [
        'nombre',
        'descripcion',
        'stock_total',
        'stock_disponible',
        'precio_renta_unitario',
        'costo_reposicion',
        'activo',
        'id_categoriamob',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio_renta_unitario' => 'decimal:2',
        'costo_reposicion' => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaMobiliario::class, 'id_categoriamob');
    }
}
