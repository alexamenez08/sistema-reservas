<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionStaff extends Model
{
    //
    protected $table = 'asignacion_staff';

    protected $fillable = [
        'rol_asignado',
        'pago_evento',
        'evento_id',
        'empleado_id',
    ];

    protected $casts = [
        'pago_evento' => 'decimal:2',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function empleado()
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }
}
