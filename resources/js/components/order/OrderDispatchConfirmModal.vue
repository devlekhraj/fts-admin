<template>
  <v-card-text class="py-6">
    <!-- <div class="text-body-1 font-weight-medium mb-2">
      Dispatch this order?
    </div> -->
    <div class="text-body-2 text-medium-emphasis mb-8">
      This will mark the order as dispatched. You can optionally add a note.
    </div>

    <v-textarea
      v-model="note"
      class="mt-4"
      density="comfortable"
      label="Dispatch note (optional)"
      placeholder="Add a note for dispatch (tracking, courier, etc.)"
      rows="3"
      auto-grow
      variant="outlined"
    />
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end ga-2">
    <v-btn variant="text" :disabled="isSubmitting" @click="emit('close')">Cancel</v-btn>
    <v-btn color="info" variant="flat" :disabled="isSubmitting" @click="confirm">
      <template #prepend>
        <v-progress-circular
          v-if="isSubmitting"
          indeterminate
          size="16"
          width="2"
          color="white"
        />
        <v-icon v-else>mdi-truck</v-icon>
      </template>
      Yes, Dispatch
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  orderId: string | number;
  action: 'dispatch';
  onSubmit: (payload: { action: 'dispatch'; note?: string }) => Promise<boolean>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const note = ref('');
const isSubmitting = ref(false);

async function confirm() {
  if (isSubmitting.value) return;
  const trimmed = note.value.trim();
  isSubmitting.value = true;
  const ok = await props.onSubmit({ action: props.action, note: trimmed || undefined });
  if (ok) emit('close');
  isSubmitting.value = false;
}
</script>
