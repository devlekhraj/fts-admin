<template>
  <div class="warranty-box">
    <div class="d-flex align-center justify-space-between" v-if="serialToShow">
      <div>
        <div class="text-caption text-medium-emphasis">Warranty Serial</div>
        <div class="warranty-row">
          <div class="warranty-code text-success">{{ serialToShow }}</div>
          <v-btn
            size="x-small"
            color="primary"
            :icon="'mdi-content-copy'"
            variant="tonal"
            @click="copySerial"
          />
        </div>
      </div>
    </div>
    <div class="d-flex align-center justify-space-between" v-else>
      <!-- <div>
        <div class="text-caption text-medium-emphasis">Warranty Serial</div>
        <div class="text-body-2 text-medium-emphasis">No warranty serial generated yet.</div>
      </div> -->
      <v-btn
        color="primary"
        variant="flat"
        prepend-icon="mdi-clipboard-check-outline"
        :loading="isSubmitting"
        :disabled="isSubmitting"
        @click="generate"
      >
        Generate Warranty
      </v-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { generateWarrantySerial } from '@/api/orders.api';
import { useSnackbar } from '@/composables/snackbar';

const props = defineProps<{
  orderId: string | number;
  serial?: string | null;
}>();

const emit = defineEmits<{
  (e: 'generated', value: string): void;
}>();

const isSubmitting = ref(false);
const serialToShow = computed(() => props.serial || '');

const { showSuccess, showError } = useSnackbar();

async function generate() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    const { warranty_token } = await generateWarrantySerial(props.orderId);
    emit('generated', warranty_token);
    showSuccess('Warranty serial generated.');
  } catch (error) {
    showError('Failed to generate warranty serial.');
    console.error(error);
  } finally {
    isSubmitting.value = false;
  }
}

async function copySerial() {
  try {
    await navigator.clipboard.writeText(serialToShow.value);
    showSuccess('Warranty serial copied.');
  } catch (error) {
    showError('Copy failed.');
    console.error(error);
  }
}
</script>

<style scoped>
.warranty-box {
  border: 1px solid rgb(var(--v-theme-outline-variant));
  border-radius: 10px;
}

.warranty-code {
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.warranty-row {
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 12px;
}
</style>
