<template>
  <AppPageHeader title="Campaigns" subtitle="Marketing campaigns" />

  <AppDataTable
    title="Campaigns"
    :headers="headers"
    :items="items"
    :total="total"
    :loading="loading"
    @update:options="onOptions"
  />
  <v-card class="mt-6" variant="outlined">
    <v-card-title>Upload Campaign File</v-card-title>
    <v-card-text>
      <v-form @submit.prevent="onSubmit">
        <v-file-input
          v-model="campaignFile"
          label="Campaign File"
          prepend-icon="mdi-paperclip"
          show-size
          clearable
          variant="outlined"
          :disabled="submitting"
        />
        <div class="mt-4">
          <v-btn color="primary" type="submit" :loading="submitting" :disabled="!selectedFile">
            Upload
          </v-btn>
        </div>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppDataTable from '@/components/datatable/AppDataTable.vue';
import type { DataTableOptions } from '@/components/datatable/types';
import { fileUpload } from '@/api/files.api';

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Status', key: 'status' },
  { title: 'Start Date', key: 'startDate' },
];

const items = ref([{ name: 'Spring Sale', status: 'Planned', startDate: '2026-03-01' }]);
const total = ref(1);
const loading = ref(false);
const submitting = ref(false);
const campaignFile = ref<File | File[] | null>(null);

const selectedFile = computed<File | null>(() => {
  if (!campaignFile.value) {
    return null;
  }

  if (Array.isArray(campaignFile.value)) {
    return campaignFile.value[0] ?? null;
  }

  return campaignFile.value;
});

function onOptions(_options: DataTableOptions) {}

async function onSubmit() {
  if (!selectedFile.value) {
    return;
  }

  submitting.value = true;
  try {
    const payload = new FormData();
    payload.append('file', selectedFile.value);
    await fileUpload(payload);
    campaignFile.value = null;
  } finally {
    submitting.value = false;
  }
}
</script>
