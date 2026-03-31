<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->char('serie', 1)->default('C')->after('estudiante_id');
            $table->integer('numero')->after('serie');
            $table->decimal('subtotal', 8, 2)->after('numero');
            $table->decimal('iva_porcentaje', 5, 2)->default(0)->after('subtotal');
            $table->decimal('iva_monto', 8, 2)->default(0)->after('iva_porcentaje');
            $table->decimal('irpf_porcentaje', 5, 2)->default(0)->after('iva_monto');
            $table->decimal('irpf_monto', 8, 2)->default(0)->after('irpf_porcentaje');
            // Mantenemos 'monto' como el total final para compatibilidad o renombramos. 
            // Usaremos 'monto' para el total final. 
            $table->text('concepto')->nullable()->after('monto');
            $table->date('fecha_evento')->nullable()->after('concepto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn([
                'serie', 'numero', 'subtotal', 'iva_porcentaje', 
                'iva_monto', 'irpf_porcentaje', 'irpf_monto', 
                'concepto', 'fecha_evento'
            ]);
        });
    }
};
