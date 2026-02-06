import type { Router } from 'vue-router';

export function applyGuards(router: Router) {
  router.beforeEach((to) => {
    const requiresAuth = Boolean(to.meta.requiresAuth);
    const token = localStorage.getItem('admin_token');

    if (requiresAuth && !token) {
      return { name: 'login' };
    }

    if (to.name === 'login' && token) {
      return { name: 'dashboard' };
    }

    return true;
  });
}
