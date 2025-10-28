<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LlegadaPlanta extends Model
{
    use HasFactory;
    protected $table = 'llegada_planta';
    protected $fillable = [
        'fecha_llegada', 
        'cantidad', 
        'proveedor', 
        'observaciones', 
        'variedad_id', 
        'operador_id'
    ];


    public function variedad()
    {
        return $this->belongsTo(Variedad::class, 'variedad_id');
    }

  
    public function operador()
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }
}