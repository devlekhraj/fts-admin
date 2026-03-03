<template>
  <v-container class="pt-0" fluid>
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="pt-10">
          <div class="text-h6 mb-1">Documents</div>
          <div class="text-body-2 text-medium-emphasis mb-4">Attached EMI request documents.</div>
        </div>
        <v-data-table :headers="headers" :items="items" density="comfortable" class="elevation-0">
          <template #item.action="{ item }">
            <div class="d-flex align-center ga-2">
              <v-btn variant="tonal" color="primary" size="x-small" icon :disabled="!item.url" @click="onView(item.url)">
                <v-icon>mdi-eye</v-icon>
              </v-btn>
              <v-btn variant="tonal" color="primary" size="x-small" icon :disabled="!item.url" @click="onDownload(item.url, item.file_name)">
                <v-icon>mdi-download</v-icon>
              </v-btn>
            </div>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { DataTableHeader } from 'vuetify';

const props = defineProps<{ data: Record<string, any> }>();

const headers: DataTableHeader[] = [
  { title: 'S.N', key: 'sn', align: 'start', width: '80' },
  { title: 'Name', key: 'name', align: 'start', width: '260' },
  { title: 'Size', key: 'size', align: 'start', width: '140' },
  { title: 'Type', key: 'type', align: 'start', width: '120' },
  { title: 'Action', key: 'action', align: 'end', sortable: false, width: '140' },
];

type DocInput = string | { url?: string; path?: string; size?: number | string; file_size?: number | string } | null | undefined;

type DocRow = {
  id: string;
  sn: number;
  name: string;
  file_name: string;
  size: string;
  type: string;
  url: string;
};

const items = computed<DocRow[]>(() => {
  const docs: Array<{ key: string; label: string; value: DocInput }> = [
    { key: 'citizenship', label: 'Citizenship', value: props.data?.citizenship },
    { key: 'salary_certificate', label: 'Salary Certificate', value: props.data?.salary_certificate },
    { key: 'bank_statement', label: 'Bank Statement', value: props.data?.bank_statement },
    { key: 'photo', label: 'Applicant Photo', value: props.data?.photo },
  ];

  const rows = docs
    .map((doc, index) => buildRow(index + 1, doc.key, doc.label, doc.value))
    .filter((row): row is DocRow => row !== null);

  return rows.length ? rows : demoRows();
});

function demoRows(): DocRow[] {
  return [
    {
      id: 'demo-citizenship',
      sn: 1,
      name: 'Citizenship',
      file_name: 'citizenship_front.jpg',
      size: '248.6 KB',
      type: 'JPG',
      url: '',
    },
    {
      id: 'demo-salary',
      sn: 2,
      name: 'Salary Certificate',
      file_name: 'salary_certificate.pdf',
      size: '1.18 MB',
      type: 'PDF',
      url: '',
    },
    {
      id: 'demo-bank',
      sn: 3,
      name: 'Bank Statement',
      file_name: 'bank_statement_march.pdf',
      size: '824.3 KB',
      type: 'PDF',
      url: '',
    },
    {
      id: 'demo-photo',
      sn: 4,
      name: 'Applicant Photo',
      file_name: 'applicant_photo.png',
      size: '136.4 KB',
      type: 'PNG',
      url: '',
    },
  ];
}

function buildRow(sn: number, id: string, label: string, input: DocInput): DocRow | null {
  if (!input) return null;

  const url = typeof input === 'string'
    ? input
    : String(input.url ?? input.path ?? '').trim();

  if (!url) return null;

  const fileName = extractFileName(url);
  const ext = extractExtension(fileName);
  const sizeValue = typeof input === 'object' && input !== null
    ? Number(input.size ?? input.file_size ?? 0)
    : 0;

  return {
    id,
    sn,
    name: label,
    file_name: fileName,
    size: sizeValue > 0 ? formatSize(sizeValue) : '-',
    type: ext || '-',
    url,
  };
}

function extractFileName(url: string): string {
  const sanitized = String(url).split('?')[0] ?? '';
  const parts = sanitized.split('/');
  return parts[parts.length - 1] || 'document';
}

function extractExtension(fileName: string): string {
  const parts = fileName.split('.');
  if (parts.length < 2) return '';
  return parts[parts.length - 1].toUpperCase();
}

function formatSize(bytes: number): string {
  if (!Number.isFinite(bytes) || bytes <= 0) return '-';
  if (bytes < 1024) return `${bytes} B`;
  const kb = bytes / 1024;
  if (kb < 1024) return `${kb.toFixed(1)} KB`;
  const mb = kb / 1024;
  return `${mb.toFixed(2)} MB`;
}

function onView(url: string) {
  if (!url) return;
  window.open(url, '_blank');
}

function onDownload(url: string, fileName: string) {
  if (!url) return;
  const link = document.createElement('a');
  link.href = url;
  link.download = fileName || 'document';
  link.click();
}
</script>
