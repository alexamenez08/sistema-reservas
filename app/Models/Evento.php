<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    //
    protected $table = 'eventos';

    protected $fillable = [
        'estado_montaje',
        'reservacion_id',
    ];

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class, 'reservacion_id');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'evento_id')
                    ->orderBy('hora_inicio', 'asc');
    }

    public function staff()
    {
        return $this->hasMany(AsignacionStaff::class, 'evento_id');
    }
}
