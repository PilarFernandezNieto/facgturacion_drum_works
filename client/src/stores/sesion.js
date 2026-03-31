import { defineStore } from 'pinia';
import api from '@/api/axios';

export const useSesionStore = defineStore('sesion', {
    state: () => ({
        usuario: null,
        token: localStorage.getItem('token_sesion') || null,
        cargando: false,
    }),
    
    getters: {
        estaAutenticado: (state) => !!state.token,
    },

    actions: {
        async iniciarSesion(credenciales) {
            this.cargando = true;
            try {
                const respuesta = await api.post('/login', credenciales);
                this.token = respuesta.data.access_token;
                this.usuario = respuesta.data.user;
                localStorage.setItem('token_sesion', this.token);
                return { exito: true };
            } catch (error) {
                console.error('Error al iniciar sesión:', error);
                return { 
                    exito: false, 
                    mensaje: error.response?.data?.message || 'Error de conexión' 
                };
            } finally {
                this.cargando = false;
            }
        },

        async registrarse(datos) {
            this.cargando = true;
            try {
                const respuesta = await api.post('/registro', datos);
                this.token = respuesta.data.access_token;
                this.usuario = respuesta.data.user;
                localStorage.setItem('token_sesion', this.token);
                return { exito: true };
            } catch (error) {
                return { 
                    exito: false, 
                    mensaje: error.response?.data?.message || 'Error al registrar' 
                };
            } finally {
                this.cargando = false;
            }
        },

        cerrarSesion() {
            api.post('/logout').catch(() => {}); // Intentar avisar al backend
            this.token = null;
            this.usuario = null;
            localStorage.removeItem('token_sesion');
        }
    }
});
