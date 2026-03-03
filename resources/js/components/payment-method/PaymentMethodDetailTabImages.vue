<template>
  <div class="pa-6">
    <div class="d-flex align-center justify-space-between mb-4">
      <div class="text-body-2 text-medium-emphasis">
        Total images: {{ imageItems.length }}
      </div>
    </div>

    <v-table v-if="imageItems.length" density="comfortable">
      <thead>
        <tr>
          <th>Image</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in imageItems" :key="item.key">
          <td class="py-3">
            <div class="table-image-preview rounded">
              <v-img v-if="item.url" :src="item.url" cover />
              <div v-else class="d-flex align-center justify-center h-100">
                <v-icon size="22" color="grey-darken-1">mdi-image-outline</v-icon>
              </div>
            </div>
          </td>
          <td class="py-3 details-col">
            <div class="text-body-2 font-weight-medium">{{ item.label }}</div>
            <div class="text-caption text-medium-emphasis">{{ item.url || '-' }}</div>
          </td>
        </tr>
      </tbody>
    </v-table>

    <div v-else class="empty-images-state">
      <v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
      <div class="text-subtitle-1 font-weight-medium mt-2">No images found</div>
      <div class="text-body-2 text-medium-emphasis">No logo image available.</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { PaymentMethodDetailResponse } from '@/api/payment-methods.api';

const props = defineProps<{
  item: PaymentMethodDetailResponse | null;
}>();

const imageItems = computed(() => {
  const logoUrl = typeof props.item?.logo_url === 'string' ? props.item.logo_url : '';
  if (!logoUrl) return [];
  return [{ key: 'logo', label: 'Logo', url: logoUrl }];
});
</script>

<style scoped>
.table-image-preview {
  width: 140px;
  height: 78px;
  background: rgb(var(--v-theme-surface-variant));
  overflow: hidden;
}

.details-col {
  min-width: 400px;
  max-width: 400px;
  word-break: break-word;
}

.empty-images-state {
  min-height: 220px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px;
}
</style>
