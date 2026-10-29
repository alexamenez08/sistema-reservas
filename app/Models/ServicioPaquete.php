<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioPaquete extends Model
{
    //
    protected $table = 'servicios_paquete';
 
    protected $fillable = [
        'id_paquete',
        'concepto',
        'precio',
    ];
 
    protected $casts = [
        'precio' => 'decimal:2',
    ];
 
    public function paquete()
    {
        return $this->belongsTo(Paquete::class, 'id_paquete');
    }
}
