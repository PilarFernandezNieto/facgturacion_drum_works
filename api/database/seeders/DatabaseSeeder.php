<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Estudiante;
use App\Models\Factura;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Usuario Admin de prueba
        User::create([
            'name' => 'Administrador Academia',
            'email' => 'admin@academia.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Crear Estudiantes de prueba
        $estudiantes = [
            ['nombre' => 'Ana Garcia', 'email' => 'ana@gmail.com', 'curso' => 'Inglés B1', 'cuota_mensual' => 60.00],
            ['nombre' => 'Carlos López', 'email' => 'carlos@gmail.com', 'curso' => 'Matemáticas', 'cuota_mensual' => 85.50],
            ['nombre' => 'Marta Sánchez', 'email' => 'marta@gmail.com', 'curso' => 'Pintura', 'cuota_mensual' => 45.00],
            ['nombre' => 'Roberto Díaz', 'email' => 'roberto@gmail.com', 'curso' => 'Guitarra', 'cuota_mensual' => 120.00],
        ];

        foreach ($estudiantes as $datos) {
            $estudiante = Estudiante::create($datos);

            // 3. Crear facturas para el mes pasado (pagadas) y mes actual (pendientes)
            Factura::create([
                'estudiante_id' => $estudiante->id,
                'monto' => $estudiante->cuota_mensual,
                'estado' => 'pagada',
                'fecha_emision' => Carbon::now()->subMonth()->toDateString(),
            ]);

            Factura::create([
                'estudiante_id' => $estudiante->id,
                'monto' => $estudiante->cuota_mensual,
                'estado' => 'pendiente',
                'fecha_emision' => Carbon::now()->toDateString(),
            ]);
        }
    }
}
