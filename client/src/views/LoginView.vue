<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useSesionStore } from '@/stores/sesion';

const sesion = useSesionStore();
const router = useRouter();

const formulario = reactive({
  email: '',
  password: ''
});

const error = ref('');
const procesando = ref(false);

async function manejarEnvio() {
  procesando.value = true;
  error.value = '';
  
  const resultado = await sesion.iniciarSesion(formulario);
  
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
        <h1 class="text-3xl font-bold text-slate-800">Academia</h1>
        <p class="text-slate-500 mt-2">Gestiona tus facturas de forma sencilla</p>
      </div>

      <form @submit.prevent="manejarEnvio" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Correo Electrónico</label>
          <input 
            v-model="formulario.email"
            type="email" 
            required
            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="ejemplo@academia.com"
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

        <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm border border-red-200">
          {{ error }}
        </div>

        <button 
          type="submit"
          :disabled="procesando"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition shadow-lg shadow-blue-200 disabled:opacity-50"
        >
          {{ procesando ? 'Iniciando sesión...' : 'Entrar al Panel' }}
        </button>
      </form>

      <div class="mt-8 text-center text-sm text-slate-600">
        ¿No tienes cuenta? 
        <router-link :to="{ name: 'registro' }" class="text-blue-600 font-semibold hover:underline">
          Regístrate aquí
        </router-link>
      </div>
    </div>
  </div>
</template>
