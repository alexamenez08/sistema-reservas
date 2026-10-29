<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anticipo extends Model
{
    protected $table = 'anticipos';

    protected $fillable = [
        'monto',
        'fecha_pago',
        'notas',
        'reservacion_id',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class, 'reservacion_id');
    }

    protected static function booted(): void
    {
        static::created(function (Anticipo $anticipo) {
            $anticipo->reservacion->recalcularSaldo();
        });

        static::deleted(function (Anticipo $anticipo) {
            $anticipo->reservacion->recalcularSaldo();
        });
    }
}

