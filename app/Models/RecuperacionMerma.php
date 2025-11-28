<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecuperacionMerma extends Model
{
    use HasFactory;

    protected $table = 'recuperacion_mermas'; 
    protected $primaryKey = 'ID_Recuperacion';
    
    
    protected $fillable = [
        'ID_Llegada', 
        'Cantidad_Recuperada',
        'Fecha_Recuperacion',
        'Observaciones',
        'Operador_Responsable', 
        'created_at',         
        'updated_at',          
    ];

    public function loteLlegada()
    {
        return $this->belongsTo(LlegadaPlanta::class, 'ID_Llegada', 'ID_Llegada');
    }
    public function operadorResponsable()
    {
        
        return $this->belongsTo(Operador::class, 'Operador_Responsable', 'ID_Operador');
    }
}
