<template>
  <v-dialog v-model="modal.isOpen" persistent :width="dialogWidth" scrollable>
    <component :is="modal.component" v-bind="modal.props" @close="modal.close()" @saved="handleSaved" />
  </v-dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useModalStore } from '@/stores/modal.store';

const modal = useModalStore();

const dialogWidth = computed(() => {
  switch (modal.size) {
    case 'sm':
      return 420;
    case 'md':
      return 640;
    case 'lg':
      return 860;
    case 'xl':
      return 1080;
    case 'full':
      return '96vw';
    default:
      return 640;
  }
});

function handleSaved(payload: unknown) {
  modal.onSaved?.(payload);
  // modal.close();
}

</script>
<style scoped></style>
