<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useSesionStore } from '@/stores/sesion';

const sesion = useSesionStore();
const router = useRouter();

const formulario = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const error = ref('');
const procesando = ref(false);

async function manejarRegistro() {
  procesando.value = true;
  error.value = '';
  
  const resultado = await sesion.registrarse(formulario);
  
  if (resultado.exito) {
    router.push({ name: 'dashboard' });
  } else {
    error.value = resultado.mensaje;
  }
  
  procesando.value = false;
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
      <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-slate-800">Crear Cuenta</h1>
        <p class="text-slate-500 mt-2">Únete para gestionar tu academia</p>
      </div>

      <form @submit.prevent="manejarRegistro" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Completo</label>
          <input 
            v-model="formulario.name"
            type="text" 
            required
            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="Juan Pérez"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Correo Electrónico</label>
          <input 
            v-model="formulario.email"
            type="email" 
            required
            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="juan@ejemplo.com"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
          <input 
            v-model="formulario.password"
            type="password" 
            required
            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="••••••••"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar Contraseña</label>
          <input 
            v-model="formulario.password_confirmation"
            type="password" 
            required
            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="••••••••"
          >
        </div>

        <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm border border-red-200">
          {{ error }}
        </div>

        <button 
          type="submit"
          :disabled="procesando"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition shadow-lg shadow-blue-200 disabled:opacity-50"
        >
          {{ procesando ? 'Registrando...' : 'Empezar ahora' }}
        </button>
      </form>

      <div class="mt-8 text-center text-sm text-slate-600">
        ¿Ya tienes cuenta? 
        <router-link :to="{ name: 'login' }" class="text-blue-600 font-semibold hover:underline">
          Inicia sesión
        </router-link>
      </div>
    </div>
  </div>
</template>
