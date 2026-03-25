<template>
  <v-btn color="primary" variant="flat" height="40" @click="open">
    <v-icon start>mdi-plus-circle</v-icon>
    Add Banner
  </v-btn>
</template>

<script setup lang="ts">
import BannerCreateModal from '@/components/banner/BannerCreateModal.vue';
import { openModal } from '@/shared/modal';

const emit = defineEmits<{ (e: 'saved', payload?: unknown): void }>();

function open() {
  openModal(
    BannerCreateModal,
    {},
    {
      title: 'Add Banner',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
