import { setupLayouts } from 'virtual:meta-layouts'
import { createRouter, createWebHistory } from 'vue-router/auto'
import { useAuthStore } from '@/stores/auth'

function recursiveLayouts(route) {
  if (route.children) {
    for (let i = 0; i < route.children.length; i++)
      route.children[i] = recursiveLayouts(route.children[i])
    
    return route
  }
  
  return setupLayouts([route])[0]
}

const router = createRouter({
  history: createWebHistory('/'),
  scrollBehavior(to) {
    if (to.hash)
      return { el: to.hash, behavior: 'smooth', top: 60 }
    
    return { top: 0 }
  },
  extendRoutes: pages => [
    ...[...pages].map(route => recursiveLayouts(route)),
  ],
})

router.beforeEach(async to => {
  const authStore = useAuthStore()

  // Restore session from stored token if we haven't loaded the user yet.
  if (!authStore.user && useCookie('accessToken').value)
    await authStore.fetchUser()

  const isPublic = to.meta.public === true

  // A device that was just signed out by the 3-device limit: clear the local
  // session and land on /login with an explanation instead of a bare bounce.
  if (!isPublic && !authStore.isAuthenticated && authKickReason.value) {
    const reason = authKickReason.value

    authKickReason.value = null
    authStore.clearLocalSession?.()

    return { name: 'login', query: { auth_notice: reason } }
  }

  if (!isPublic && !authStore.isAuthenticated)
    return { name: 'login', query: { redirect: to.fullPath } }

  // Login / register / forgot-password are for guests only
  if (to.meta.guestOnly && authStore.isAuthenticated)
    return { name: 'root' }

  if (to.meta.requiresAdmin && !authStore.isAdmin)
    return { name: 'root' }

  return true
})

export { router }
export default function (app) {
  app.use(router)
}
