<template>
  <AppPageHeader title="Attributes" subtitle="Product attributes" />
  <AppDataTable :headers="headers" :items="items" :total="total" :loading="loading">
    <template #item.attributes_count="{ item }">
      <v-chip size="small" variant="tonal" color="primary" label>
        {{ Number(item.attributes_count ?? 0) }} attributes
      </v-chip>
    </template>
    <template #item.created_at="{ item }">
      <span>{{ formatLongDate(item.created_at) ?? '-' }}</span>
    </template>
    <template #item.action="{ item }">
      <div class="d-flex align-center justify-end ga-2">
        <v-btn size="small" variant="flat" color="primary"
          :to="{ name: 'admin.product.attributes.detail', params: { id: item.id } }">
          details
        </v-btn>
        <v-btn size="small" variant="flat" color="error">
          Delete
        </v-btn>
      </div>
    </template>
  </AppDataTable>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import { listAttributeProducts, type AttributeProductItem } from '@/api/attribute-products.api';
import { formatLongDate } from '@/shared/utils';

const headers: { title: string; key: string; sortable?: boolean; align?: 'end' | 'start' | 'center' }[] = [
  // { title: 'ID', key: 'id' },
  { title: 'Name', key: 'name' },
  { title: 'Attributes Count', key: 'attributes_count' },
  { title: 'Created', key: 'created_at' },
  { title: 'Action', key: 'action', sortable: false, align: 'end' },
];

const items = ref<AttributeProductItem[]>([]);
const total = ref(0);
const loading = ref(false);

async function fetchAttributes() {
  loading.value = true;
  try {
    const response = await listAttributeProducts();
    const list = Array.isArray(response?.data) ? response.data : [];
    items.value = list;
    total.value = list.length;
  } finally {
    loading.value = false;
  }
}

onMounted(fetchAttributes);
</script>
