import { defineStore } from 'pinia';

export type SnackbarColor = 'success' | 'error' | 'info' | 'warning';

export type SnackbarOptions = {
	message: string;
	color?: SnackbarColor;
	timeout?: number;
	location?: string;
};

export const useSnackbarStore = defineStore('snackbar', {
	state: () => ({
		visible: false,
		message: '' as string,
		color: 'success' as SnackbarColor,
		timeout: 3000 as number,
		location: 'top right' as string,
	}),
	actions: {
		show(options: SnackbarOptions) {
			this.message = options.message;
			this.color = options.color ?? 'success';
			this.timeout = options.timeout ?? 3000;
			this.location = options.location ?? 'top right';
			this.visible = true;
		},
		hide() {
			this.visible = false;
		},
	},
});
