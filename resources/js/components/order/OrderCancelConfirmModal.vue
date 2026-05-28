<template>
  <v-card-text class="py-6">
    <div class="text-body-2 text-medium-emphasis">
      This will mark the order as <strong>canceled</strong>. A reason is required.
    </div>

    <v-textarea
      v-model="reason"
      class="mt-4"
      label="Reason"
      placeholder="Write the reason for cancellation..."
      rows="3"
      auto-grow
      variant="outlined"
      :error="submitted && reason.trim().length === 0"
      :error-messages="submitted && reason.trim().length === 0 ? ['Reason is required'] : []"
    />
  </v-card-text>

  <v-card-actions class="pb-4 d-flex justify-end ga-2">
    <v-btn variant="text" :disabled="isSubmitting" @click="emit('close')">Cancel</v-btn>
    <v-btn
      color="error"
      variant="flat"
      :disabled="isSubmitting || reason.trim().length === 0"
      @click="confirm"
    >
      <template #prepend>
        <v-progress-circular
          v-if="isSubmitting"
          indeterminate
          size="16"
          width="2"
          color="white"
        />
        <v-icon v-else>mdi-cancel</v-icon>
      </template>
      Yes, Cancel
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  orderId: string | number;
  onSubmit: (payload: { action: 'cancel'; reason: string }) => Promise<boolean>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const reason = ref('');
const submitted = ref(false);
const isSubmitting = ref(false);

async function confirm() {
  submitted.value = true;
  const trimmed = reason.value.trim();
  if (!trimmed) return;
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  const ok = await props.onSubmit({ action: 'cancel', reason: trimmed });
  if (ok) emit('close');
  isSubmitting.value = false;
}
</script>
