<template>
  <v-card-text class="py-6">
    <div class="text-body-2 text-medium-emphasis">
      {{ message }}
    </div>
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end ga-2">
    <v-btn variant="text" :disabled="isSubmitting" @click="emit('close')">Cancel</v-btn>
    <v-btn :color="color" variant="flat" :disabled="isSubmitting" @click="confirm">
      <template #prepend>
        <v-progress-circular v-if="isSubmitting" indeterminate size="16" width="2" color="white" />
        <v-icon v-else>{{ icon }}</v-icon>
      </template>
      {{ confirmLabel }}
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  heading: string;
  message: string;
  confirmLabel: string;
  color: string;
  icon: string;
  onSubmit: () => Promise<boolean>;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const isSubmitting = ref(false);

async function confirm() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  const ok = await props.onSubmit();
  if (ok) emit('close');
  isSubmitting.value = false;
}
</script>

