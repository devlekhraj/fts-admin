<template>

  <v-card-text>
    <v-radio-group v-model="selected">
      <v-radio v-for="status in statuses" :key="status" :label="status" :value="status" />
    </v-radio-group>
  </v-card-text>
  <v-card-actions class="d-flex justify-end ga-2">
    <v-btn variant="text" @click="close">Cancel</v-btn>
    <v-btn color="primary" variant="flat" @click="save">Save</v-btn>
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

const statuses = ['Draft', 'Placed', 'Confirmed', 'Dispatched', 'Completed', 'Canceled'];
const selected = ref<string>(props.currentStatus || statuses[0]);

watch(
  () => props.currentStatus,
  (val) => {
    if (val) selected.value = val;
  }
);

function close() {
  closeModal();
}

const isSubmitting = ref(false);
const statusCode = computed(() => {
  const map: Record<string, number> = {
    Draft: 0,
    Placed: 1,
    Confirmed: 2,
    Dispatched: 3,
    Completed: 4,
    Canceled: 5,
  };
  return map[selected.value] ?? 0;
});

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
