<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    //
    protected $table = 'actividades';

    protected $fillable = [
        'actividad',
        'descripcion',
        'hora_inicio', /* => ['required', 'date'], */
        'hora_fin',    /* => ['required', 'date', 'after:hora_inicio'], */
        'evento_id',
        'responsable',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fin'    => 'datetime',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function responsableUsuario()
    {
        return $this->belongsTo(User::class, 'responsable');
    }
}
