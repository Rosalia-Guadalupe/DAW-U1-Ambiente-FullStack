
import {
  createRouter,
  createWebHistory
} from 'vue-router'

import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import ServicesView from '../views/ServicesView.vue'
import {useAuth} from '../services/auth'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: {
      hideHeader: true
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/servicios',
    name: 'servicios',
    component: ServicesView,
    meta: {
      requiresAuth: true
    }
  },

  // ETAPA 24: Administración de usuarios
  {
    path: '/usuarios',
    name: 'usuarios',
    component: () =>
      import('../views/usuarios/UsersView.vue'),
    meta: {
      requiresAuth: true,
      roles: [
        'ADMIN_SISTEMA'
      ]
    }
  },

  // ETAPA 24: Administración de servicios
  {
    path: '/servicios/admin',
    name: 'servicios-admin',
    component: () =>
      import('../views/servicios/ServicesAdminView.vue'),
    meta: {
      requiresAuth: true,
      roles: [
        'ADMIN_SISTEMA',
        'ADMIN_SERVICIOS'
      ]
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(
  async (to) => {
    const {
      initialized,
      authenticated,
      user,
      checkSession
    } = useAuth()

    if (!initialized.value) {
      await checkSession()
    }

    if (to.meta.requiresAuth && !authenticated.value) {
      return {
        name: 'login',
        query: {
          redirect: to.fullPath
        }
      }
    }

    // ETAPA 25: Validar permisos por rol
    const requiredRoles = to.meta.roles

    if (requiredRoles && !requiredRoles.includes(user.value?.rol)) {
      return {
        name: 'dashboard'
      }
    }

    if (to.name === 'login' && authenticated.value) {
      return {
        name: 'dashboard'
      }
    }
  }
)

// NUEVO: Exportar el router
export default router
