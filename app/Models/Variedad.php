<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variedad extends Model
{
    use HasFactory;

   
    protected $table = 'variedades';


    protected $primaryKey = 'ID_Variedad';
    public $incrementing = true;


    protected $fillable = [
        'nombre',
        'especie', 
        'color',   
        'codigo',
    ];
}
