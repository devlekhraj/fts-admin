<template>
  <AppPageHeader title="Developer" subtitle="Developer tools and utilities" />

  <v-card class="mt-4 pa-4">
    <v-data-table
      :headers="headers"
      :items="items"
      :loading="loading"
      item-value="id">
      <template #item.sn="{ index }">
        <span>{{ index + 1 }}</span>
      </template>

      <template #item.host="{ item }">
        <span style="font-size: 0.8rem;">{{ item.host || '-' }}</span>
      </template>

      <template #item.mode="{ item }">
        <v-chip size="small" label variant="tonal" color="primary">
          {{ item.mode || '-' }}
        </v-chip>
      </template>

      <template #item.live_public_key="{ item }">
        <div class="d-flex align-center ga-2">
          <span class="text-caption text-medium-emphasis text-truncate key-preview">
            {{ item.live_public_key }}
          </span>
          <v-btn
            icon
            size="x-small"
            variant="tonal"
            color="primary"
            :disabled="!item.live_public_key"
            @click="copyLivePublicKey(item.live_public_key)">
            <v-icon size="14">mdi-content-copy</v-icon>
          </v-btn>
        </div>
      </template>

      <template #item.is_active="{ item }">
        <v-chip size="small" label variant="tonal" :color="item.is_active ? 'success' : 'warning'">
          {{ item.is_active ? 'Active' : 'Inactive' }}
        </v-chip>
      </template>

      <template #item.created_at="{ item }">
        <span>{{ formatLongDate(item.created_at) ?? '-' }}</span>
      </template>

      <template #item.action="{ item }">
        <v-btn icon size="x-small" variant="tonal" color="primary" @click="onConfigure(item)">
          <v-icon size="16">mdi-cog</v-icon>
        </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { listApiKeys, type ApiKeyItem } from '@/api/developer.api';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { formatLongDate } from '@/shared/utils';
import { useSnackbarStore } from '@/stores/snackbar.store';

const headers = [
  { title: 'ID', key: 'sn', sortable: false, minWidth: '90' },
  { title: 'Host', key: 'host', sortable: false, minWidth: '260' },
  { title: 'Mode', key: 'mode', sortable: false, minWidth: '120' },
  { title: 'Live Public Key', key: 'live_public_key', sortable: false, minWidth: '280' },
  { title: 'Description', key: 'description', sortable: false, minWidth: '220' },
  { title: 'Status', key: 'is_active', sortable: false, minWidth: '120' },
  { title: 'Created At', key: 'created_at', sortable: false, minWidth: '180' },
  { title: 'Action', key: 'action', sortable: false, minWidth: '90' },
];

const items = ref<ApiKeyItem[]>([]);
const loading = ref(false);
const snackbar = useSnackbarStore();

function maskKey(value: string | null | undefined): string {
  const text = String(value ?? '').trim();
  if (!text) return '-';
  if (text.length <= 12) return text;
  return `${text.slice(0, 8)}...${text.slice(-4)}`;
}

async function copyLivePublicKey(value: string | null | undefined) {
  const text = String(value ?? '').trim();
  if (!text) return;

  try {
    await navigator.clipboard.writeText(text);
    snackbar.show({ message: 'Live public key copied.', color: 'success' });
  } catch {
    snackbar.show({ message: 'Failed to copy live public key.', color: 'error' });
  }
}

async function fetchApiKeys() {
  loading.value = true;
  try {
    items.value = await listApiKeys();
  } finally {
    loading.value = false;
  }
}

function onConfigure(item: ApiKeyItem) {
  snackbar.show({ message: `Configure API key #${item.id}`, color: 'info' });
}

onMounted(fetchApiKeys);
</script>

<style scoped>
.key-preview {
  min-width: 400px;
}
</style>
