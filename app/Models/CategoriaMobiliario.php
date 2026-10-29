<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaMobiliario extends Model
{
    //
    protected $table = 'categorias_mobiliario';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function mobiliario()
    {
        return $this->hasMany(Mobiliario::class, 'id_categoriamob');
    }

}
