<template>
  <v-btn icon size="x-small" variant="tonal" color="error" @click="open">
    <v-icon size="16">mdi-delete</v-icon>
  </v-btn>
</template>

<script setup lang="ts">
import BannerDeleteModal from '@/components/banner/BannerDeleteModal.vue';
import { openModal } from '@/shared/modal';

type Banner = {
  id?: number | string;
  name?: string | null;
  slug?: string | null;
};

const props = defineProps<{ banner: Banner }>();
const emit = defineEmits<{ (e: 'deleted', payload?: unknown): void }>();

function open() {
  openModal(
    BannerDeleteModal,
    { banner: props.banner },
    {
      title: 'Confirm Banner Deletion',
      size: 'sm',
      onSaved: (payload: unknown) => emit('deleted', payload),
    },
  );
}
</script>
