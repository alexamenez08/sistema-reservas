<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservacion extends Model
{
   use SoftDeletes;
 
    protected $table = 'reservaciones';
 
    protected $fillable = [
        'dia_evento',
        'hora_inicio',
        'hora_fin',
        'contador_invitados',
        'costo_total',
        'descuento',
        'motivo_descuento',
        'saldo_pendiente',
        'status',
        'cliente_id',
        'locacion_id',
        'paquete_id',
    ];
 
    protected $casts = [
        'dia_evento'      => 'date',
        'hora_inicio'     => 'datetime',
        'hora_fin'        => 'datetime',
        'costo_total'     => 'decimal:2',
        'descuento'       => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];
 
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
 
    public function locacion()
    {
        return $this->belongsTo(Locacion::class, 'locacion_id');
    }
 
    public function paquete()
    {
        return $this->belongsTo(Paquete::class, 'paquete_id');
    }
 
    public function anticipos()
    {
        return $this->hasMany(Anticipo::class, 'reservacion_id');
    }
 
    public function evento()
    {
        return $this->hasOne(Evento::class, 'reservacion_id');
    }
 
    public function recalcularSaldo(): void
    {
        $totalAnticipado = $this->anticipos()->sum('monto');
        $this->saldo_pendiente = $this->costo_total - $totalAnticipado;
        $this->save();
    }
}