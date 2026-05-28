<template>
  <v-card-text class="py-6">
    <div class="text-body-1 font-weight-medium mb-2">
      {{ heading }}
    </div>
    <div class="text-body-2 text-medium-emphasis">
      {{ message }}
    </div>
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end ga-2">
    <v-btn variant="text" :disabled="isSubmitting" @click="emit('close')">Cancel</v-btn>
    <v-btn :color="confirmColor" variant="flat" :disabled="isSubmitting" @click="confirm">
      <template #prepend>
        <v-progress-circular
          v-if="isSubmitting"
          indeterminate
          size="16"
          width="2"
          color="white"
        />
        <v-icon v-else>{{ confirmIcon }}</v-icon>
      </template>
      {{ confirmLabel }}
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { ref } from 'vue';

const props = defineProps<{
  orderId: string | number;
  action: 'confirm' | 'dispatch' | 'deliver';
  onSubmit: (payload: { action: 'confirm' | 'dispatch' | 'deliver' }) => Promise<boolean>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const heading = computed(() => {
  if (props.action === 'confirm') return 'Confirm this order?';
  if (props.action === 'dispatch') return 'Dispatch this order?';
  return 'Mark this order as delivered?';
});

const message = computed(() => {
  if (props.action === 'confirm') return 'This will mark the order as confirmed. Continue?';
  if (props.action === 'dispatch') return 'This will mark the order as dispatched. Continue?';
  return 'This will mark the order as delivered. Continue?';
});

const confirmLabel = computed(() => {
  if (props.action === 'confirm') return 'Yes, Confirm';
  if (props.action === 'dispatch') return 'Yes, Dispatch';
  return 'Yes, Delivered';
});

const confirmColor = computed(() => (props.action === 'confirm' ? 'success' : 'info'));
const confirmIcon = computed(() => {
  if (props.action === 'confirm') return 'mdi-check-circle';
  if (props.action === 'dispatch') return 'mdi-truck';
  return 'mdi-package-variant-closed';
});

const isSubmitting = ref(false);

async function confirm() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  const ok = await props.onSubmit({ action: props.action });
  if (ok) emit('close');
  isSubmitting.value = false;
}
</script>
