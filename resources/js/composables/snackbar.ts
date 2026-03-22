import { useSnackbarStore } from '@/stores/snackbar.store';

export function useSnackbar() {
	const snackbarStore = useSnackbarStore();

	const showSuccess = (message: string) => {
		snackbarStore.show({ message, color: 'success' });
	};

	const showError = (message: string) => {
		snackbarStore.show({ message, color: 'error' });
	};

	const showInfo = (message: string) => {
		snackbarStore.show({ message, color: 'info' });
	};

	const showWarning = (message: string) => {
		snackbarStore.show({ message, color: 'warning' });
	};

	return {
		showSuccess,
		showError,
		showInfo,
		showWarning,
	};
}
