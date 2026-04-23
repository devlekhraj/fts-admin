<template>
  <v-btn color="primary" variant="flat" @click="open">
    <v-icon start>mdi-image-plus-outline</v-icon>
    New Banner
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
      title: 'New Banner',
      size: 'md',
      onSaved: (payload: unknown) => emit('saved', payload),
    },
  );
}
</script>
