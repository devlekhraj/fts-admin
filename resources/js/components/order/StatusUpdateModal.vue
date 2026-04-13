<template>

  <v-card-text>
    <v-radio-group v-model="selected">
      <v-radio
        v-for="status in statuses"
        :key="status.label"
        :label="status.label"
        :value="status.label"
        :disabled="status.code <= currentCode"
      >
        <template #label>
          <div class="d-flex flex-column py-2">
            <span class="text-body-2 d-flex align-center ga-2">
              <v-icon v-if="status.code <= currentCode" size="16" color="success">mdi-check</v-icon>
              {{ status.label }}
            </span>
            <span class="text-caption text-medium-emphasis">{{ status.subtitle }}</span>
          </div>
        </template>
      </v-radio>
    </v-radio-group>
  </v-card-text>
  <v-card-actions class="d-flex justify-end ga-2">
    <!-- <v-btn variant="text" @click="close">Cancel</v-btn> -->
    <v-btn color="primary" variant="flat" @click="save" class="px-4" :disabled="isSubmitting">
      <template #prepend>
        <v-progress-circular v-if="isSubmitting" indeterminate size="18" width="2" color="white" />
        <v-icon v-else start>mdi-check-circle-outline</v-icon>
      </template>
      Update
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { closeModal } from '@/shared/modal';
import { updateOrderStatus } from '@/api/orders.api';

const props = defineProps<{
  orderId: string | number;
  currentStatus?: string;
}>();

const emit = defineEmits<{
  (e: 'saved', value: string): void;
}>();

const statuses = [
  { label: 'Draft', code: 0, subtitle: 'Created but not placed' },
  { label: 'Placed', code: 1, subtitle: 'Order submitted by customer' },
  { label: 'Confirmed', code: 2, subtitle: 'Validated by admin' },
  { label: 'Dispatched', code: 3, subtitle: 'Out for delivery/shipping' },
  { label: 'Completed', code: 4, subtitle: 'Delivered to customer' },
  { label: 'Canceled', code: 5, subtitle: 'Canceled by admin/customer' },
];

const currentCode = computed(() => {
  const match = statuses.find((s) => s.label === props.currentStatus);
  return match ? match.code : 0;
});

const nextSelectable = computed(() => statuses.find((s) => s.code > currentCode.value));

const selected = ref<string>(nextSelectable.value?.label || props.currentStatus || statuses[0].label);

watch(
  () => props.currentStatus,
  (val) => {
    if (val) {
      const next = statuses.find((s) => s.code > currentCode.value);
      selected.value = next?.label || val;
    }
  }
);

function close() {
  closeModal();
}

const isSubmitting = ref(false);
const statusCode = computed(() => statuses.find((s) => s.label === selected.value)?.code ?? 0);

async function save() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    await updateOrderStatus(props.orderId, statusCode.value);
    emit('saved', selected.value);
    closeModal();
  } finally {
    isSubmitting.value = false;
  }
}
</script>
