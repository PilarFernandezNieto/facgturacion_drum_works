<script setup>
import { ref, onMounted, reactive, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useFacturaStore } from "@/stores/factura";
import { storeToRefs } from "pinia";
import { useClienteStore } from "@/stores/cliente";

import { confirmDialog, notifyError, toast } from "@/utils/swal";
import ScreenLoader from "@/components/ui/ScreenLoader.vue";
import FacturaComponent from "@/components/ui/FacturaComponent.vue";

const clienteStore = useClienteStore();
const facturaStore = useFacturaStore();

const { clientes } = storeToRefs(clienteStore);
const {
  isLoading,
  generando,
  filtroMes,
  filtroEstado,
  mesesDisponibles,
  facturasFiltradas,
} = storeToRefs(facturaStore);

const mostrarModalBolo = ref(false);

const formularioBolo = reactive({
  cliente_id: "",
  subtotal: 0,
  iva_porcentaje: 10,
  irpf_porcentaje: 15,
  concepto: "",
  fecha_evento: "",
  fecha_emision: new Date().toISOString().split("T")[0],
});

const totalBolo = computed(() => {
  const base = parseFloat(formularioBolo.subtotal) || 0;
  const iva = (base * formularioBolo.iva_porcentaje) / 100;
  const irpf = (base * formularioBolo.irpf_porcentaje) / 100;
  return (base + iva - irpf).toFixed(2);
});

async function cargarDatos() {
  try {
    await Promise.all([
      facturaStore.cargarFacturas(),
      clienteStore.cargarClientes(),
    ]);
  } catch (error) {
    console.error("Error al inicializar datos:", error);
  }
}

async function generarMasiva() {
  const result = await confirmDialog(
    "Generar Facturas",
    "Se van a generar las facturas de todos los alumnos de CLASES para el mes actual. ¿Continuar?",
    "info",
  );
  if (!result.isConfirmed) return;

  try {
    const r = await facturaStore.generarMasiva();
    toast(r.mensaje);
  } catch (error) {
    notifyError("Error", "No se pudieron generar las facturas.");
  }
}

async function guardarBolo() {
  try {
    await facturaStore.guardarBolo(formularioBolo);
    toast("Factura B generada con éxito");
    mostrarModalBolo.value = false;
    // Limpiar formulario
    formularioBolo.cliente_id = "";
    formularioBolo.subtotal = 0;
    formularioBolo.concepto = "";
    formularioBolo.fecha_evento = "";
  } catch (error) {
    notifyError("Error", "Error al guardar factura de bolo.");
  }
}

async function eliminarFactura(id) {
  const result = await confirmDialog(
    "¿Eliminar factura?",
    "¿Estás seguro de eliminar esta factura permanentemente?",
    "warning",
  );
  if (!result.isConfirmed) return;

  try {
    await facturaStore.eliminarFactura(id);
    toast("Factura eliminada");
  } catch (e) {
    notifyError("Error", "No se pudo eliminar.");
  }
}

async function toggleEstado(factura) {
  try {
    const { nuevoEstado } = await facturaStore.cambiarEstado(
      factura.id,
      factura.estado,
    );
    toast(`Factura marcada como ${nuevoEstado}`);
  } catch (error) {
    notifyError("Error", "No se pudo cambiar el estado.");
  }
}

function formatearFecha(fecha) {
  return new Date(fecha).toLocaleDateString("es-ES", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

onMounted(cargarDatos);
</script>

<template>
  <div v-if="isLoading">
    <ScreenLoader />
  </div>

  <div v-else class="space-y-6 overflow-x-hidden">
    <!-- Cabecera -->
    <div
      class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center"
    >
      <div>
        <h2>Control de Facturación</h2>
        <p class="text-sm text-slate-500">
          Gestiona tus ingresos de clases (C) y bolos (B).
        </p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row">
        <button
          @click="mostrarModalBolo = true"
          class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-bold transition shadow-lg shadow-purple-100 flex items-center justify-center gap-2 text-sm"
        >
          🎸 Nueva Factura Bolo
        </button>
        <button
          @click="generarMasiva"
          :disabled="generando"
          class="bg-principal hover:bg-principal-hover text-white px-4 py-2 rounded-lg font-bold transition shadow-lg shadow-principal-100 disabled:opacity-50 flex items-center justify-center gap-2 text-sm"
        >
          <span v-if="!generando">⚡ Generar Clases (Mes)</span>
          <span v-else>Generando...</span>
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <div
      class="bg-white p-4 rounded-xl border border-slate-200 flex flex-wrap gap-6 items-center shadow-sm"
    >
      <div class="flex items-center gap-3">
        <label
          class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
          >Filtrar por Mes</label
        >

        <select
          v-model="filtroMes"
          class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium cursor-pointer"
        >
          <option value="">Todos los meses</option>
          <option v-for="m in mesesDisponibles" :key="m" :value="m">
            {{ facturaStore.nombreMes(m) }}
          </option>
        </select>
      </div>

      <div class="flex items-center gap-3 border-l border-slate-100 pl-6">
        <label
          class="text-[10px] font-black text-slate-400 uppercase tracking-widest"
          >Estado de Pago</label
        >
        <select
          v-model="filtroEstado"
          class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-bold cursor-pointer"
        >
          <option value="">Cualquier estado</option>
          <option value="pendiente" class="text-amber-600 font-bold">
            PENDIENTE
          </option>
          <option value="pagada" class="text-emerald-600 font-bold">
            PAGADA
          </option>
        </select>
      </div>

      <button
        v-if="filtroMes || filtroEstado"
        @click="
          filtroMes = '';
          filtroEstado = '';
        "
        class="ml-auto text-xs font-bold text-red-500 hover:text-principal flex items-center gap-1 transition"
      >
        ✕ Limpiar filtros
      </button>
    </div>

    <!-- Lista de Facturas -->

    <div
      class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
    >
      <FacturaComponent
        v-for="factura in facturasFiltradas"
        :key="factura.id"
        :factura="factura"
        @toggle-estado="toggleEstado"
        @eliminar="eliminarFactura"
      />
    </div>

    <!-- Modal Factura Bolo -->
    <div
      v-if="mostrarModalBolo"
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center sm:p-4 z-50 overflow-hidden"
    >
      <div
        class="bg-white rounded-t-2xl sm:rounded-2xl w-[90%] lg:w-[55%] shadow-2xl animate-in fade-in slide-in-from-bottom-4 sm:zoom-in duration-200 max-h-[95dvh] flex flex-col"
      >
        <div
          class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-2xl"
        >
          <h3 class="text-xl font-bold text-slate-800">
            Nueva Factura de Bolo (Serie B)
          </h3>
          <button
            @click="mostrarModalBolo = false"
            class="text-slate-400 hover:text-slate-600 text-xl"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="guardarBolo" novalidate class="p-6 space-y-4">
          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
              <label class="block text-sm font-medium text-slate-700 mb-1"
                >Cliente (Entidad/Organizador)</label
              >
              <select
                v-model="formularioBolo.cliente_id"
                required
                class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-purple-500"
              >
                <option value="" disabled>Selecciona un cliente...</option>
                <option
                  v-for="cliente in clientes.filter((e) => e.tipo === 'bolo')"
                  :key="cliente.id"
                  :value="cliente.id"
                >
                  {{ cliente.nombre }}
                  <span v-if="cliente.nif_cif">({{ cliente.nif_cif }})</span>
                </option>
              </select>
              <p
                v-if="!clientes.some((e) => e.tipo === 'bolo')"
                class="text-xs text-amber-600 mt-1 italic"
              >
                * Primero debes crear un cliente de tipo "Bolo" en la sección
                Clientes.
              </p>
            </div>

            <div class="col-span-12">
              <label class="block text-sm font-medium text-slate-700 mb-1"
                >Concepto del Concierto</label
              >
              <textarea
                v-model="formularioBolo.concepto"
                required
                rows="2"
                placeholder="Ej: Concierto de Los Commodoros en La Puerta de Cimadevilla..."
                class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-purple-500"
              ></textarea>
            </div>

            <div class="col-span-12 lg:col-span-6">
              <label class="block text-sm font-medium text-slate-700 mb-1"
                >Fecha del Concierto</label
              >
              <input
                v-model="formularioBolo.fecha_evento"
                type="date"
                required
                class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-purple-500"
              />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <label class="block text-sm font-medium text-slate-700 mb-1"
                >Fecha Emisión</label
              >
              <input
                v-model="formularioBolo.fecha_emision"
                type="date"
                required
                class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-purple-500"
              />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <label class="block text-sm font-medium text-slate-700 mb-1"
                >Base Imponible (€)</label
              >
              <input
                v-model="formularioBolo.subtotal"
                type="number"
                step="0.01"
                required
                class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-purple-500 font-mono font-bold"
              />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <label class="block text-sm font-medium text-slate-700 mb-1"
                >Cálculo Fiscal</label
              >
              <div
                class="text-xs text-slate-500 bg-slate-50 p-2 rounded border border-slate-100"
              >
                IVA: 10% | IRPF: 15%
              </div>
            </div>
          </div>

          <div
            class="bg-purple-50 p-4 rounded-xl border border-purple-100 flex justify-between items-center"
          >
            <div class="text-purple-700 font-medium">Líquido a Percibir:</div>
            <div class="text-2xl font-black text-purple-900">
              {{ totalBolo }} €
            </div>
          </div>

          <div class="pt-4 flex gap-3">
            <button
              type="button"
              @click="mostrarModalBolo = false"
              class="flex-1 px-4 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold shadow-md shadow-purple-100"
            >
              Emitir Factura Serie B
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
