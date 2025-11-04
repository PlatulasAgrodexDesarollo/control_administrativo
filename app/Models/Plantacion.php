<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantacion extends Model
{
    use HasFactory;
    
    protected $table = 'plantacion';
    protected $primaryKey = 'ID_Plantacion'; 
    public $incrementing = true; 
    

    protected $fillable = [
        'Fecha_Plantacion', 
        'Plantas_Plantadas', 
        'Observaciones', 
        'ID_Llegada',        
        'ID_Variedad',         
        'Operador_Plantacion_ID'
    ];


    public function loteLlegada()
    {
        return $this->belongsTo(LlegadaPlanta::class, 'ID_Llegada', 'ID_Llegada');
    }
    
    
    public function operadorPlantacion()
    {
        return $this->belongsTo(Operador::class, 'Operador_Plantacion', 'ID_Operador');
    }
    

    public function variedad()
    {
        return $this->belongsTo(Variedad::class, 'ID_Variedad', 'ID_Variedad');
    }

    public function operadorLlegada()
    {
        return $this->belongsTo(Operador::class, 'Operador_Llegada', 'ID_Operador');
    }
    
 
}