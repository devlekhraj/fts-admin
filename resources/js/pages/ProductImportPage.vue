<template>
  <AppPageHeader title="Import Products" subtitle="Upload a file to import products">
    <template #actions>
      <v-btn variant="outlined" color="primary" prepend-icon="mdi-arrow-left" @click="goBack()">
        Back to Products
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <div class="text-subtitle-1 mb-2">Import file</div>
    <div class="text-medium-emphasis mb-4">
      Upload a CSV/Excel file and we’ll import products into the catalog.
    </div>

    <v-file-input
      label="Choose file"
      prepend-icon="mdi-paperclip"
      accept=".csv,.xlsx,.xls"
      :error-messages="errorMessage ? [errorMessage] : []"
      @update:model-value="onFileSelected"
    />

    <v-alert v-if="selectedFile" variant="tonal" type="info" class="mt-4">
      Selected: <strong>{{ selectedFile.name }}</strong>
    </v-alert>

    <v-alert v-if="!selectedFile" variant="tonal" type="warning" class="mt-4">
      Choose a file to preview its rows.
    </v-alert>

    <v-alert v-if="selectedFile && !errorMessage && tableRows.length === 0" variant="tonal" type="warning" class="mt-4">
      No rows found in this file.
    </v-alert>

    <v-card v-if="tableRows.length > 0 && columnMeta.length > 0" class="mt-6" variant="outlined">
      <v-card-title class="text-subtitle-1">
        Preview ({{ tableRows.length }} rows)
      </v-card-title>
      <v-divider />
      <div class="table-scroll">
        <v-table density="comfortable">
          <thead>
            <tr>
              <th v-for="(col, i) in columnMeta" :key="i" class="text-left">
                {{ col.title }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, rIndex) in tableRows" :key="rIndex">
              <td v-for="(col, cIndex) in columnMeta" :key="`${rIndex}-${cIndex}`">
                <template v-if="col.kind === 'name_with_image'">
                  <div class="d-flex align-center ga-3">
                    <v-avatar size="34" color="grey-lighten-3" rounded>
                      <v-img
                        v-if="row[col.imageIndex] && looksLikeUrl(row[col.imageIndex])"
                        :src="row[col.imageIndex]"
                        :alt="row[col.sourceIndex] || 'Product image'"
                      />
                      <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
                    </v-avatar>
                    <div class="">
                      {{ row[col.sourceIndex] ?? '' }}
                    </div>
                  </div>
                </template>
                <template v-else-if="col.kind === 'image'">
                  <div class="d-flex align-center ga-2">
                    <!-- <v-avatar size="34" color="grey-lighten-3" rounded>
                      <v-img v-if="row[col.sourceIndex] && looksLikeUrl(row[col.sourceIndex])" :src="row[col.sourceIndex]" alt="Preview" />
                      <v-icon v-else size="18" color="grey-darken-1">mdi-image-outline</v-icon>
                    </v-avatar> -->
                    <span class="text-truncate">{{ row[col.sourceIndex] ?? '' }}</span>
                  </div>
                </template>
                <template v-else>
                  {{ row[col.sourceIndex] ?? '' }}
                </template>
              </td>
            </tr>
          </tbody>
        </v-table>
      </div>
    </v-card>

    <div class="d-flex ga-3 mt-4">
      <v-btn color="primary" prepend-icon="mdi-upload" disabled>
        Start Import
      </v-btn>
      <v-btn variant="text" color="primary" prepend-icon="mdi-download-outline" disabled>
        Download Template
      </v-btn>
    </div>
  </v-card>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import * as XLSX from 'xlsx';
import { ref } from 'vue';

const router = useRouter();

function goBack() {
  router.push({ name: 'admin.product.list' });
}

const selectedFile = ref<File | null>(null);
const errorMessage = ref<string>('');
const tableHeaders = ref<string[]>([]);
const tableRows = ref<string[][]>([]);
type ColumnMeta =
  | { title: string; sourceIndex: number; kind: 'text' }
  | { title: string; sourceIndex: number; kind: 'image' }
  | { title: string; sourceIndex: number; imageIndex: number; kind: 'name_with_image' };

const columnMeta = ref<ColumnMeta[]>([]);

function looksLikeUrl(value: string): boolean {
  const v = value.trim();
  if (!v) return false;
  if (v.startsWith('http://') || v.startsWith('https://')) return true;
  if (v.startsWith('//')) return true;
  if (v.startsWith('/')) return true;
  return false;
}

function normalizeCell(value: unknown): string {
  if (value === null || value === undefined) return '';
  if (typeof value === 'string') return value;
  if (typeof value === 'number' || typeof value === 'boolean' || typeof value === 'bigint') return String(value);
  if (value instanceof Date) return value.toISOString();
  try {
    return JSON.stringify(value);
  } catch {
    return String(value);
  }
}

function buildHeaderNames(rawHeaderRow: unknown[], columnCount: number): string[] {
  const headers = Array.from({ length: columnCount }).map((_, idx) => {
    const maybe = normalizeCell(rawHeaderRow[idx]);
    return maybe.trim() || `Column ${idx + 1}`;
  });
  return headers;
}

async function onFileSelected(input: File | File[] | null) {
  const file = Array.isArray(input) ? input[0] ?? null : input;
  selectedFile.value = file ?? null;
  errorMessage.value = '';
  tableHeaders.value = [];
  tableRows.value = [];
  columnMeta.value = [];

  if (!file) return;

  try {
    const buffer = await file.arrayBuffer();
    const workbook = XLSX.read(buffer, { type: 'array', raw: true });
    const firstSheetName = workbook.SheetNames?.[0];
    if (!firstSheetName) return;

    const sheet = workbook.Sheets[firstSheetName];
    const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, blankrows: false }) as unknown[][];
    if (!Array.isArray(rows) || rows.length === 0) return;

    const columnCount = rows.reduce((max, row) => Math.max(max, Array.isArray(row) ? row.length : 0), 0);
    if (columnCount === 0) return;

    const headerRow = Array.isArray(rows[0]) ? rows[0] : [];
    tableHeaders.value = buildHeaderNames(headerRow, columnCount);
    tableRows.value = rows.slice(1).map((row) => {
      const cells = Array.isArray(row) ? row : [];
      return Array.from({ length: columnCount }).map((_, idx) => normalizeCell(cells[idx]));
    });

    const headerKey = (idx: number) => (tableHeaders.value[idx] ?? '').trim().toLowerCase().replace(/\s+/g, '_');
    const nameIndex = tableHeaders.value.findIndex((_, idx) => ['name', 'product_name', 'title'].includes(headerKey(idx)));
    const imageIndex = tableHeaders.value.findIndex((_, idx) =>
      ['image_urls', 'image', 'imageurl', 'thumbnail', 'thumb', 'thumb_url', 'photo_url'].includes(headerKey(idx)),
    );

    columnMeta.value = tableHeaders.value.map((title, sourceIndex) => {
      if (imageIndex >= 0 && sourceIndex === imageIndex) return { title, sourceIndex, kind: 'image' } as ColumnMeta;
      if (nameIndex >= 0 && imageIndex >= 0 && sourceIndex === nameIndex) {
        return { title, sourceIndex, imageIndex, kind: 'name_with_image' } as ColumnMeta;
      }
      return { title, sourceIndex, kind: 'text' } as ColumnMeta;
    });
  } catch (err: any) {
    errorMessage.value = err?.message || 'Failed to read file';
  }
}
</script>

<style scoped lang="scss">
.table-scroll {
  max-height: 520px;
  overflow: auto;
  font-size: small;
  td{
    font-size: small;
  }
}

.text-truncate {
  max-width: 360px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
