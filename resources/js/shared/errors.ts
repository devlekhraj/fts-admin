export function getErrorMessage(error: unknown) {
  // The server's own message first ("You do not have permission to do this", "You cannot delete your own account", ...).
  const serverMessage = (error as { response?: { data?: { message?: unknown } } })?.response?.data?.message;
  if (typeof serverMessage === 'string' && serverMessage.trim() !== '') return serverMessage;
  if (error instanceof Error) return error.message;
  return 'Unexpected error';
}
