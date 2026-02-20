import type { Component } from 'vue';
import { useModalStore, type ModalOptions } from '@/stores/modal.store';

export function openModal(component: Component, props: Record<string, unknown> = {}, options: ModalOptions = {}) {
  const modal = useModalStore();
  modal.open(component, props, options);
}

export function closeModal() {
  const modal = useModalStore();
  modal.close();
}
