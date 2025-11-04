<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operador extends Model
{
    use HasFactory;
    
    // Nombre de la tabla
    protected $table = 'operadores'; 
    
    
    protected $primaryKey = 'ID_Operador';
    public $incrementing = true; 

    
    protected $fillable = [
        'nombre', 
        'apellido',
        'puesto',
        'estado',
    ];
}