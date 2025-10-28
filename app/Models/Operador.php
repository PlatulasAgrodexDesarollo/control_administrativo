<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operador extends Model
{
    use HasFactory;
    
    // Indica el nombre de la tabla en la base de datos
    protected $table = 'operadores'; 

    // Define los campos que se pueden llenar masivamente (Mass Assignment)
    protected $fillable = [
        'nombre',
        'apellido',
        'puesto',
        'observaciones',
        'estado',
    ];
}