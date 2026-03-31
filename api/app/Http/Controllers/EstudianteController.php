<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    /**
     * Listar todos los estudiantes.
     */
    public function index()
    {
        return response()->json(Estudiante::all());
    }

    /**
     * Almacenar un nuevo estudiante.
     */
    public function store(Request $request)
    {
        $reglas = [
            'nombre' => 'required|string|max:255',
            'nif_cif' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:100',
            'cuota_mensual' => 'required|numeric|min:0',
            'tipo' => 'required|in:clase,bolo',
        ];

        $datosValidados = $request->validate($reglas);

        $estudiante = Estudiante::create($datosValidados);

        return response()->json([
            'mensaje' => 'Registro guardado correctamente.',
            'estudiante' => $estudiante
        ], 201);
    }

    /**
     * Mostrar un estudiante específico.
     */
    public function show(Estudiante $estudiante)
    {
        return response()->json($estudiante);
    }

    /**
     * Actualizar los datos de un estudiante.
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        $reglas = [
            'nombre' => 'sometimes|required|string|max:255',
            'nif_cif' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:100',
            'cuota_mensual' => 'sometimes|required|numeric|min:0',
            'tipo' => 'sometimes|required|in:clase,bolo',
        ];

        $datosValidados = $request->validate($reglas);

        $estudiante->update($datosValidados);

        return response()->json([
            'mensaje' => 'Datos actualizados correctamente.',
            'estudiante' => $estudiante
        ]);
    }

    /**
     * Eliminar un estudiante.
     */
    public function destroy(Estudiante $estudiante)
    {
        $estudiante->delete();

        return response()->json([
            'mensaje' => 'Estudiante eliminado correctamente.'
        ]);
    }
}
