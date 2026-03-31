<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'nif_cif',
        'email',
        'telefono',
        'direccion',
        'curso',
        'cuota_mensual',
        'tipo' // clase o bolo
    ];

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
}
