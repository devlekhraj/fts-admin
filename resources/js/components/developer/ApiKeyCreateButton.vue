<template>
  <v-btn color="primary" variant="flat" prepend-icon="mdi-plus" @click="open">
    Create API Key
  </v-btn>
</template>

<script setup lang="ts">
import ApiKeyFormModal from '@/components/developer/ApiKeyFormModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    ApiKeyFormModal,
    { mode: 'create' },
    {
      title: 'Create API Key',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
