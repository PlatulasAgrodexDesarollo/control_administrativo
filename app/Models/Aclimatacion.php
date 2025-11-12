<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aclimatacion extends Model
{
    use HasFactory;
    
    protected $table = 'aclimatacion';
    protected $primaryKey = 'ID_Aclimatacion'; 
    public $incrementing = true; 
    
    protected $fillable = [
        'Fecha_Ingreso', 
        'Estado_Inicial', 
        'Duracion_Aclimatacion', 
        'Observaciones',
        'ID_Plantacion',         
        'ID_Variedad',           
        'Operador_Responsable'   
    ];


    public function plantacion()
    {
        return $this->belongsTo(Plantacion::class, 'ID_Plantacion', 'ID_Plantacion');
    }
    
    
    public function variedad()
    {
        return $this->belongsTo(Variedad::class, 'ID_Variedad', 'ID_Variedad');
    }

    
    public function operadorResponsable()
    {
        return $this->belongsTo(Operador::class, 'Operador_Responsable', 'ID_Operador');
    }
}