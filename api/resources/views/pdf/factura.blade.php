<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $codigo_factura }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; color: #000; line-height: 1.4; margin: 0; padding: 10px; }
        
        .header-container { width: 100%; margin-bottom: 50px; }
        .logo-box { width: 180px; display: inline-block; vertical-align: top; }
        .emisor-box { text-align: right; display: inline-block; width: 350px; float: right; margin-top: 50px; }
        
        .title-factura { color: #cc0000; font-size: 32px; font-weight: bold; text-align: right; margin-bottom: 10px; }
        
        .linea-separadora { border-top: 1px solid #cc0000; margin-top: 10px; margin-bottom: 30px; }
        
        .datos-bloque { width: 100%; margin-bottom: 40px; }
        .cliente-box { width: 45%; display: inline-block; vertical-align: top; }
        .info-factura-box { width: 45%; float: right; text-align: right; }
        
        .etiqueta { text-decoration: underline; font-weight: bold; font-size: 14px; margin-bottom: 5px; }
        
        .tabla-desc { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabla-desc th { border-bottom: 1px solid #cc0000; text-align: left; padding: 10px 0; font-size: 14px; text-transform: uppercase; }
        .tabla-desc td { padding: 15px 0; font-size: 14px; border-bottom: 1px solid #eeeeee; }
        
        .resumen-box { margin-top: 20px; }
        .barra-total { background-color: #cc0000; color: #ffffff; padding: 8px 15px; font-weight: bold; font-size: 16px; margin-top: 5px; }
        .flex-box { display: block; width: 100%; }
        
        .footer-iban { margin-top: 60px; border-top: 1px solid #cc0000; padding-top: 10px; text-align: right; font-size: 14px; }
        .iban-text { font-family: monospace; font-weight: bold; }
        
        /* Layout específico para impuestos */
        .tax-row { width: 100%; text-align: right; margin-bottom: 3px; font-size: 14px; }
        .tax-label { display: inline-block; width: 150px; font-weight: bold; }
        .tax-value { display: inline-block; width: 100px; }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="logo-box">
             <!-- Logo alternativo basado en CSS si no hay imagen -->
             <div style="text-align: center;">
                <span style="font-size: 60px; color: #cc0000;">★</span><br>
                <div style="background: #000; color: #fff; padding: 5px; font-weight: bold; margin-top: -15px;">DeCalle</div>
                <div style="color: #cc0000; font-size: 10px; font-weight: bold; border-top: 1px solid #cc0000;">DRUM WORKS</div>
             </div>
        </div>
        <div class="emisor-box">
            <div class="title-factura">FACTURA</div>
            <div class="linea-separadora"></div>
            <strong>Guillermo Carlos González Fernández</strong><br>
            N.I.F: 09439818C<br>
            C/ Francisco Eiriz 20, 1º Dcha<br>
            33212 Gijón
        </div>
    </div>

    <div class="datos-bloque">
        <div class="cliente-box">
            <div class="etiqueta">DATOS CLIENTE</div>
            <strong>{{ $estudiante->nombre }}</strong><br>
            @if($estudiante->nif_cif) DNI/NIF: {{ $estudiante->nif_cif }} @endif<br>
            @if($estudiante->direccion) {{ $estudiante->direccion }} @endif
        </div>
        <div class="info-factura-box">
            <strong>Nº Factura:</strong> {{ $codigo_factura }}<br>
            <strong>Fecha:</strong> {{ $fecha }}
        </div>
    </div>

    <div class="etiqueta">DESCRIPCIÓN</div>
    <table class="tabla-desc">
        <tbody>
            <tr>
                <td>{{ $factura->concepto }} @if($factura->fecha_evento) ({{ \Carbon\Carbon::parse($factura->fecha_evento)->format('d/m/Y') }}) @endif</td>
                <td style="text-align: right; width: 100px;">{{ number_format($factura->subtotal, 2) }} €</td>
            </tr>
        </tbody>
    </table>

    <div class="resumen-box">
        @if($factura->iva_porcentaje > 0 || $factura->irpf_porcentaje > 0)
            <div class="tax-row">
                <span class="tax-label">BASE IMPONIBLE</span>
                <span class="tax-value">{{ number_format($factura->subtotal, 2) }} €</span>
            </div>
            @if($factura->iva_porcentaje > 0)
            <div class="tax-row">
                <span class="tax-label">IVA ({{ number_format($factura->iva_porcentaje, 0) }}%)</span>
                <span class="tax-value">{{ number_format($factura->iva_monto, 2) }} €</span>
            </div>
            @endif
            @if($factura->irpf_porcentaje > 0)
            <div class="tax-row">
                <span class="tax-label">IRPF ({{ number_format($factura->irpf_porcentaje, 0) }}%)</span>
                <span class="tax-value">{{ number_format($factura->irpf_monto, 2) }} €</span>
            </div>
            @endif
        @endif

        <div class="barra-total">
            <div style="float: left;">IMPORTE TOTAL @if($factura->iva_porcentaje > 0) <span style="font-size: 10px; font-weight: normal;">(B.I. + IVA - IRPF)</span> @endif</div>
            <div style="text-align: right;">{{ number_format($factura->monto, 2) }} €</div>
        </div>
    </div>

    <div class="footer-iban">
        <strong>Nº cuenta para el ingreso</strong><br>
        <span class="iban-text">ES83 2100 5700 0502 0015 7257</span>
    </div>
</body>
</html>
