<template>
  <BaseDetailTabImages
    :files="imageItems"
    usage-type="payment_methods"
    :usage-id="item?.id ?? null"
    :edit-modal="null"
    empty-state-message="No logo image available."
    @updated="emit('updated')"
  >
    <template #headers>
      <th>Primary Image</th>
    </template>
    <template #details="{ file }">
      <div class="text-body-2 font-weight-medium">{{ file.label }}</div>
      <div class="text-caption text-medium-emphasis">{{ file.url || '-' }}</div>
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

const props = defineProps<{
  item: PaymentMethodDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const imageItems = computed(() => {
  const logoUrl = typeof props.item?.logo_url === 'string' ? props.item.logo_url : '';
  if (!logoUrl) return [];
  return [
    {
      id: 'logo',
      key: 'logo',
      label: 'Logo',
      url: logoUrl,
      meta: { is_default: true },
    },
  ];
});
</script>
