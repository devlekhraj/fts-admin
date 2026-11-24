import { http } from './http';

/** Your own account. Open to every signed-in admin, whatever their role. */
export function updateOwnName(name: string) {
  return http.put('/admin/account/profile', { name });
}

export function changeOwnPassword(currentPassword: string, password: string, confirmPassword: string) {
  return http.put('/admin/account/password', {
    current_password: currentPassword,
    password,
    confirm_password: confirmPassword,
  });
}
