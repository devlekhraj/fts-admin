<template>
  <v-card-text class="py-6">

    <div class="text-body-2 text-medium-emphasis">
      This will mark the order as completed. Continue?
    </div>
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end ga-2">
    <v-btn variant="text" :disabled="isSubmitting" @click="emit('close')">Cancel</v-btn>
    <v-btn color="success" variant="flat" :disabled="isSubmitting" @click="confirm">
      <template #prepend>
        <v-progress-circular
          v-if="isSubmitting"
          indeterminate
          size="16"
          width="2"
          color="white"
        />
        <v-icon v-else>mdi-check-circle-outline</v-icon>
      </template>
      Yes, Complete
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  orderId: string | number;
  action: 'complete';
  onSubmit: (payload: { action: 'complete' }) => Promise<boolean>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const isSubmitting = ref(false);

async function confirm() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  const ok = await props.onSubmit({ action: props.action });
  if (ok) emit('close');
  isSubmitting.value = false;
}
</script>
