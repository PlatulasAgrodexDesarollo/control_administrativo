<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mysql_principal';

    // El nombre de la tabla en el proyecto principal
    protected $table = 'operadores';

    // Tu llave primaria personalizada
    protected $primaryKey = 'ID_Operador';

    protected $fillable = [
        'Nombre', 'Apellido_P', 'Apellido_M', 'Puesto', 
        'Usuario', 'Contrasena_Hash', 'current_session_id', 
        'last_activity', 'Activo', 'ID_Rol'
    ];

    public function getAuthPassword()
    {
        return $this->Contrasena_Hash;
    }

    // Desactivamos los timestamps si la tabla no tiene 'created_at' y 'updated_at'
    public $timestamps = false;
}