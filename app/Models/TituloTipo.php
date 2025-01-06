<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TituloTipo extends Model
{
 
    protected $connection = 'mysql';

    // Definir la tabla asociada
    protected $table = 'titulos_tipos';

    // Habilitar asignación masiva para estos campos
    protected $fillable = [
        'nombre',
    ];

    // Opcional: desactivar timestamps si no los usas
     public $timestamps = true;
}
