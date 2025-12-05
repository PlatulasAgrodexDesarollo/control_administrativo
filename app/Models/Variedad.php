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

    public function aclimataciones()
{
    return $this->belongsToMany(
        \App\Models\Aclimatacion::class,
        'aclimatacion_variedad',
        'variedad_id',      
        'aclimatacion_id'   
    )
    ->withPivot('cantidad_plantas', 'ID_llegada');
}
}
