import { createRouter, createWebHistory } from 'vue-router';
import { useSesionStore } from '@/stores/sesion';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { libre: true }
    },
    {
      path: '/registro',
      name: 'registro',
      component: () => import('../views/RegistroView.vue'), // Crear después
      meta: { libre: true }
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiereAuth: true }
    },
    {
      path: '/estudiantes',
      name: 'estudiantes',
      component: () => import('../views/EstudiantesView.vue'),
      meta: { requiereAuth: true }
    },
    {
      path: '/facturas',
      name: 'facturas',
      component: () => import('../views/FacturasView.vue'),
      meta: { requiereAuth: true }
    }
  ]
});

// Guardia de navegación para proteger rutas
router.beforeEach((to, from, next) => {
  const sesion = useSesionStore();
  
  if (to.meta.requiereAuth && !sesion.estaAutenticado) {
    next({ name: 'login' });
  } else if (to.meta.libre && sesion.estaAutenticado) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
});

export default router;
