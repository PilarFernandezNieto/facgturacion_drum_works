<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller
{
    public function index()
    {
        return response()->json(Factura::with('estudiante')->orderBy('fecha_emision', 'desc')->get());
    }

    /**
     * Generación masiva de la serie C (Clases)
     */
    public function generarMasiva(Request $request)
    {
        $estudiantes = Estudiante::where('tipo', 'clase')->get();
        $fechaActual = Carbon::now();
        $anio = $fechaActual->year;
        $conteo = 0;

        foreach ($estudiantes as $estudiante) {
            $existe = Factura::where('estudiante_id', $estudiante->id)
                ->where('serie', 'C')
                ->whereMonth('fecha_emision', $fechaActual->month)
                ->whereYear('fecha_emision', $fechaActual->year)
                ->exists();

            if (!$existe) {
                // Obtener siguiente número de serie C para el año actual
                $ultimoNumero = Factura::where('serie', 'C')
                    ->whereYear('fecha_emision', $anio)
                    ->max('numero') ?? 0;

                $mesNombre = $fechaActual->translatedFormat('F');
                $concepto = "Clases batería " . strtoupper($mesNombre);

                Factura::create([
                    'estudiante_id' => $estudiante->id,
                    'serie' => 'C',
                    'numero' => $ultimoNumero + 1,
                    'subtotal' => $estudiante->cuota_mensual,
                    'iva_porcentaje' => 0,
                    'iva_monto' => 0,
                    'irpf_porcentaje' => 0,
                    'irpf_monto' => 0,
                    'monto' => $estudiante->cuota_mensual,
                    'concepto' => $concepto,
                    'estado' => 'pendiente',
                    'fecha_emision' => $fechaActual->toDateString(),
                ]);
                $conteo++;
            }
        }

        return response()->json([
            'mensaje' => "Se han generado $conteo facturas nuevas para la serie C.",
            'total_generadas' => $conteo
        ]);
    }

    /**
     * Generación manual (principalmente para Serie B - Bolos)
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'serie' => 'required|in:C,B',
            'subtotal' => 'required|numeric',
            'iva_porcentaje' => 'required|numeric',
            'irpf_porcentaje' => 'required|numeric',
            'concepto' => 'required|string',
            'fecha_evento' => 'nullable|date',
            'fecha_emision' => 'required|date',
        ]);

        $anio = Carbon::parse($datos['fecha_emision'])->year;
        
        // Numeración correlativa
        $ultimoNumero = Factura::where('serie', $datos['serie'])
            ->whereYear('fecha_emision', $anio)
            ->max('numero') ?? 0;
        
        $ivaMonto = ($datos['subtotal'] * $datos['iva_porcentaje']) / 100;
        $irpfMonto = ($datos['subtotal'] * $datos['irpf_porcentaje']) / 100;
        $total = $datos['subtotal'] + $ivaMonto - $irpfMonto;

        $factura = Factura::create([
            'estudiante_id' => $datos['estudiante_id'],
            'serie' => $datos['serie'],
            'numero' => $ultimoNumero + 1,
            'subtotal' => $datos['subtotal'],
            'iva_porcentaje' => $datos['iva_porcentaje'],
            'iva_monto' => $ivaMonto,
            'irpf_porcentaje' => $datos['irpf_porcentaje'],
            'irpf_monto' => $irpfMonto,
            'monto' => $total,
            'concepto' => $datos['concepto'],
            'fecha_evento' => $datos['fecha_evento'],
            'fecha_emision' => $datos['fecha_emision'],
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'mensaje' => 'Factura generada correctamente',
            'factura' => $factura
        ], 201);
    }

    public function actualizarEstado(Request $request, Factura $factura)
    {
        $request->validate(['estado' => 'required|in:pendiente,pagada']);
        $factura->update(['estado' => $request->estado]);
        return response()->json(['mensaje' => 'Estado actualizado.', 'factura' => $factura]);
    }

    public function destroy(Factura $factura)
    {
        $factura->delete();
        return response()->json(['mensaje' => 'Factura eliminada.']);
    }

    public function exportarPdf(Factura $factura)
    {
        $factura->load('estudiante');
        
        $datos = [
            'fecha' => Carbon::parse($factura->fecha_emision)->format('d/m/Y'),
            'factura' => $factura,
            'estudiante' => $factura->estudiante,
            // Agregamos el código formateado para la vista
            'codigo_factura' => $factura->codigo
        ];

        $pdf = Pdf::loadView('pdf.factura', $datos);
        
        // Nombre del archivo basado en el código
        $nombreArchivo = "FRA " . str_replace('/', '-', $factura->codigo) . ".pdf";
        
        return $pdf->download($nombreArchivo);
    }
}
