<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChequeoHyT extends Model
{
    use HasFactory;
    
    protected $table = 'chequeo_hyt';
    protected $primaryKey = 'ID_CheqHyT'; 
    public $incrementing = true; 
    
    
    protected $fillable = [
        'Fecha_Chequeo', 
        'Hora_Chequeo',
        'Temperatura', 
        'Hr',
        'Lux',
        'Actividades',
        'Observaciones', 
        'ID_Aclimatacion',       
        'Operador_Responsable' 
    ];

   
    public function aclimatacion()
    {
        return $this->belongsTo(Aclimatacion::class, 'ID_Aclimatacion', 'ID_Aclimatacion');
    }
    
   
    public function operadorResponsable()
    {
        return $this->belongsTo(Operador::class, 'Operador_Responsable_ID', 'ID_Operador');
    }
}