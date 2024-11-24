<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model
{
    use SoftDeletes;
    protected $table = 'encuestas';
    public $incrementing = true;

    protected $fillable = [
        'id_mesa', 'valoracion_restaurante', 'valoracion_mozo', 'valoracion_cocinero', 'breve_descripcion'
    ];
}