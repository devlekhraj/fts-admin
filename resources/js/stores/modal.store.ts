import { defineStore } from 'pinia';
import { markRaw } from 'vue';
import type { Component } from 'vue';

export type ModalSize = 'sm' | 'md' | 'lg' | 'xl' | 'full';

export type ModalOptions = {
	title?: string;
	size?: ModalSize;
	persistent?: boolean;
	onSaved?: (payload?: unknown) => void;
};

export const useModalStore = defineStore('modal', {
	state: () => ({
		isOpen: false,
		component: null as Component | null,
		props: {} as Record<string, unknown>,
		title: '' as string,
		size: 'md' as ModalSize,
		persistent: true,
		onSaved: null as ((payload?: unknown) => void) | null,
	}),
	actions: {
		open(component: Component, props: Record<string, unknown> = {}, options: ModalOptions = {}) {
			this.component = markRaw(component);
			this.props = props;
			this.title = options.title ?? '';
			this.size = options.size ?? 'md';
			this.persistent = options.persistent ?? true;
			this.onSaved = options.onSaved ?? null;
			this.isOpen = true;
		},
		close() {
			this.isOpen = false;
		},
		reset() {
			this.component = null;
			this.props = {};
			this.title = '';
			this.size = 'md';
			this.persistent = true;
			this.onSaved = null;
		},
	},
});
