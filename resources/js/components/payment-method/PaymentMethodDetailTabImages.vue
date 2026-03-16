<template>
  <BaseDetailTabImages
    :files="paymentMethodFiles"
    usage-type="payment_methods"
    :usage-id="item?.id ?? null"
    directory="payment-methods"
    :edit-modal="PaymentMethodImageEditModal"
    :edit-modal-props="(file: any) => ({ paymentMethodId: item?.id ?? null, file })"
    edit-modal-title="Edit Payment Logo"
    empty-state-message="No logos attached to this payment method."
    @updated="emit('updated')"
  >
    <template #headers>
      <th>Primary Image</th>
    </template>
    <template #rows="{ file }">
      <td class="py-3">
        <v-chip
          size="small"
          label
          variant="tonal"
          :color="file.meta?.is_default ? 'primary' : 'default'">
          {{ file.meta?.is_default ? 'Yes' : 'No' }}
        </v-chip>
      </td>
    </template>
  </BaseDetailTabImages>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { PaymentMethodDetailResponse } from '@/api/payment-methods.api';
import BaseDetailTabImages from '@/components/media/BaseDetailTabImages.vue';
import PaymentMethodImageEditModal from '@/components/payment-method/PaymentMethodImageEditModal.vue';

const props = defineProps<{
  item: PaymentMethodDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const paymentMethodFiles = computed(() => props.item?.images ?? []);
</script>
