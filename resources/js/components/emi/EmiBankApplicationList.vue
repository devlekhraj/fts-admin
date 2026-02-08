<template>
  <v-card elevation="0" class="pa-4">
    <div class="info-card">
      <div class="d-flex align-center justify-space-between mb-2">
        <div>
          <h4>Generated Applications</h4>
        </div>
        <v-btn color="primary" @click="modalGenerate()">
          <v-icon left>mdi-file-plus</v-icon>
          Generate Now
        </v-btn>
      </div>
      <v-divider class="my-2" />
      <v-data-table :headers="headers" :items="items" density="comfortable" class="elevation-0">
        <template #item.emailSent="{ item }">
          <div class="text-right">
            <v-chip :color="item.emailSent ? 'green' : 'grey'" text-color="white" size="small">
              {{ item.emailSent ? 'Yes' : 'No' }} ({{ item.emailCount }})
            </v-chip>
          </div>
        </template>
        <template #item.action>
          <div class="d-flex justify-end gap-2">
            <v-btn variant="tonal" color="primary" class="mr-4" size="x-small" icon>
              <v-icon>mdi-eye</v-icon>
            </v-btn>
            <v-btn variant="tonal" color="primary" size="x-small" icon>
              <v-icon>mdi-download</v-icon>
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </div>

  </v-card>
</template>

<script setup lang="ts">
import { useModalStore } from '@/stores/modal.store';
import EmiApplicationForm from '@/components/emi/EmiApplicationForm.vue'
import type { DataTableHeader } from 'vuetify';

const headers: DataTableHeader[] = [
  { title: 'Bank', key: 'bank', align: 'start' },
  { title: 'Date', key: 'date', align: 'end' },
  { title: 'Email Sent (Count)', key: 'emailSent', align: 'end' },
  { title: 'Action', key: 'action', align: 'end', sortable: false },
];

const items = [
  { bank: 'Nabil Bank', date: '--', emailSent: false, emailCount: 0 },
  { bank: 'Siddhartha Bank', date: '--', emailSent: true, emailCount: 1 },
];

const props = defineProps<{ data: Record<string, any> }>();
const modal = useModalStore();

function modalGenerate() {
  modal.open(
    EmiApplicationForm,
    { id: props.data?.id },
    {
      size: 'lg',
      // onSaved: (payload) => {
      //   console.log('saved from modal', {payload});
      // },
    },
  );
}


</script>
