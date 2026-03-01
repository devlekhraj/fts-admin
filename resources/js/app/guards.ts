import type { Router } from 'vue-router';

export function applyGuards(router: Router) {
  router.beforeEach((to) => {
    const requiresAuth = Boolean(to.meta.requiresAuth);
    const token = localStorage.getItem('admin_token');

    if (requiresAuth && !token) {
      return { name: 'admin.login' };
    }

    if (to.name === 'admin.login' && token) {
      return { name: 'admin.dashboard' };
    }

    return true;
  });
}
