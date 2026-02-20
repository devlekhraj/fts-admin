<template>
  <v-dialog
    v-model="modal.isOpen"
    :persistent="modal.persistent ?? true"
    :width="dialogWidth"
    scrollable
    @after-leave="modal.reset()"
  >
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between py-0">
        <span style="font-size:medium;">{{ modal.title }}</span>
        <v-btn icon  variant="text" color="error" @click="modal.close()">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-divider></v-divider>
      <component :is="modal.component" v-bind="modal.props" @close="modal.close()" @saved="handleSaved" />
    </v-card>
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
