<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    //
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'apepat',
        'apemat',
        'correo',
        'tel',
    ];

    public function reservaciones(){
        return $this->hasMany(Reservacion::class, 'cliente_id');
    }

    protected function nombreCompleto(): Attribute
{
    return Attribute::make(
        get: fn () => $this->nombre . ' ' . $this->apepat . ($this->apemat ? ' ' . $this->apemat : '')
    );
}
}
