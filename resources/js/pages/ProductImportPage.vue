<template>
  <AppPageHeader title="Import Products" subtitle="Upload a file to import products">
    <template #actions>
      <v-btn variant="outlined" color="primary" prepend-icon="mdi-arrow-left" @click="goBack()">
        Back to Products
      </v-btn>
    </template>
  </AppPageHeader>

  <v-card class="pa-6">
    <v-file-input
      variant="outlined"
      prepend-icon=""
      prepend-inner-icon="mdi-paperclip"
      accept=".csv,.xlsx,.xls"
      :error-messages="errorMessage ? [errorMessage] : []"
      @update:model-value="onFileSelected"
      style="max-width: 600px"
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

    <v-alert v-if="importError" variant="tonal" type="error" class="mt-4">
      {{ importError }}
    </v-alert>

    <v-alert v-if="importSummary" variant="tonal" type="success" class="mt-4">
      <div class="font-weight-medium mb-2">{{ importMessage }}</div>
      <div class="d-flex flex-wrap ga-2">
        <v-chip size="small" color="primary" label>Processed {{ importSummary.processed }}</v-chip>
        <v-chip size="small" color="success" label>Created {{ importSummary.created }}</v-chip>
        <v-chip size="small" color="info" label>Updated {{ importSummary.updated }}</v-chip>
        <v-chip size="small" color="warning" label>Skipped {{ importSummary.skipped }}</v-chip>
        <v-chip size="small" color="error" label>Failed {{ importSummary.failed }}</v-chip>
      </div>
    </v-alert>

    <v-card v-if="visibleImportResults.length > 0" class="mt-4" variant="outlined">
      <v-card-title class="text-subtitle-1">
        Row Results
      </v-card-title>
      <v-divider />
      <v-list density="comfortable">
        <v-list-item
          v-for="result in visibleImportResults"
          :key="result.row"
          :class="{ 'result-link': Boolean(result.product_id) }"
          @click="openProductDetail(result.product_id)">
          <template #prepend>
            <v-chip size="small" :color="statusColor(result.status)" label>
              {{ result.status }}
            </v-chip>
          </template>
          <v-list-item-title>
            <span v-if="result.product_name" class="font-weight-medium">{{ result.product_name }}</span>
            <span v-else>Row {{ result.row }}</span>
          </v-list-item-title>
          <v-list-item-subtitle>
            <span v-if="result.product_id !== null && result.product_id !== undefined">
              ID: {{ result.product_id }}
            </span>
            <span v-if="result.price !== null && result.price !== undefined" class="ml-2">
              Price: {{ formatPrice(result.price) }}
            </span>
            <span v-if="result.match_field" class="ml-2">
              Matched by {{ result.match_field }}
            </span>
            <span v-if="result.reason || result.error" class="ml-2">
              {{ result.reason || result.error }}
            </span>
            <span v-if="result.warnings?.length" class="ml-2">
              {{ result.warnings.join(' | ') }}
            </span>
          </v-list-item-subtitle>
          <v-table v-if="result.changes?.length" density="compact" class="mt-3 changes-table">
            <thead>
              <tr>
                <th class="text-left">Field</th>
                <th class="text-left">Previous Value</th>
                <th class="text-left">Updated Value</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="change in result.changes" :key="`${result.row}-${change.field}`">
                <td>{{ change.field }}</td>
                <td>{{ formatChangeValue(change.previous) }}</td>
                <td>{{ formatChangeValue(change.current) }}</td>
              </tr>
            </tbody>
          </v-table>
        </v-list-item>
      </v-list>
    </v-card>

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
      <v-btn
        color="primary"
        prepend-icon="mdi-upload"
        :loading="isImporting"
        :disabled="!canImport"
        @click="onStartImport">
        Start Import
      </v-btn>
      <v-btn variant="text" color="primary" prepend-icon="mdi-download-outline" disabled>
        Download Template
      </v-btn>
    </div>
  </v-card>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import * as XLSX from 'xlsx';
import { importProducts, type ImportProductsResponse, type ImportProductsRow } from '@/api/products.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

const router = useRouter();
const snackbar = useSnackbarStore();

function goBack() {
  router.push({ name: 'admin.product.list' });
}

const selectedFile = ref<File | null>(null);
const errorMessage = ref<string>('');
const importError = ref<string>('');
const tableHeaders = ref<string[]>([]);
const headerKeys = ref<string[]>([]);
const tableRows = ref<string[][]>([]);
const importRows = ref<ImportProductsRow[]>([]);
const isImporting = ref(false);
const importSummary = ref<{
  processed: number;
  created: number;
  updated: number;
  skipped: number;
  failed: number;
} | null>(null);
const importResults = ref<Array<{
  row: number;
  status: 'created' | 'updated' | 'skipped' | 'failed';
  product_id?: number | string | null;
  product_name?: string | null;
  price?: number | string | null;
  match_field?: 'id' | 'sku' | null;
  changes?: Array<{
    field: string;
    previous?: unknown;
    current?: unknown;
  }>;
  reason?: string;
  error?: string;
  warnings?: string[];
}>>([]);
const IMPORT_BATCH_SIZE = 5;

type ColumnMeta =
  | { title: string; sourceIndex: number; kind: 'text' }
  | { title: string; sourceIndex: number; kind: 'image' }
  | { title: string; sourceIndex: number; imageIndex: number; kind: 'name_with_image' };

const columnMeta = ref<ColumnMeta[]>([]);
const visibleImportResults = computed(() => importResults.value.filter((result) => result.status === 'created' || result.status === 'updated'));
const importMessage = computed(() => {
  if (!importSummary.value) {
    return '';
  }

  return `Import finished. ${importSummary.value.created} created, ${importSummary.value.updated} updated, ${importSummary.value.skipped} skipped, ${importSummary.value.failed} failed.`;
});
const canImport = computed(() => {
  return Boolean(selectedFile.value && tableRows.value.length > 0 && columnMeta.value.length > 0 && !errorMessage.value && !isImporting.value);
});

function statusColor(status: 'created' | 'updated' | 'skipped' | 'failed'): string {
  switch (status) {
    case 'created':
      return 'success';
    case 'updated':
      return 'info';
    case 'skipped':
      return 'warning';
    case 'failed':
      return 'error';
    default:
      return 'primary';
  }
}

function formatPrice(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  const num = typeof value === 'number' ? value : Number(value);
  if (Number.isNaN(num)) {
    return String(value);
  }

  return num.toLocaleString(undefined, {
    minimumFractionDigits: Number.isInteger(num) ? 0 : 2,
    maximumFractionDigits: 2,
  });
}

function formatChangeValue(value: unknown): string {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  if (Array.isArray(value)) {
    return value.length > 0 ? value.map((item) => formatChangeValue(item)).join(', ') : '-';
  }

  if (typeof value === 'object') {
    try {
      return JSON.stringify(value);
    } catch {
      return String(value);
    }
  }

  return String(value);
}

function chunkRows<T>(rows: T[], size: number): T[][] {
  const chunks: T[][] = [];
  for (let index = 0; index < rows.length; index += size) {
    chunks.push(rows.slice(index, index + size));
  }
  return chunks;
}

function openProductDetail(productId: number | string | null | undefined) {
  if (productId === null || productId === undefined || productId === '') {
    return;
  }

  const route = router.resolve({ name: 'admin.product.detail', params: { id: productId } });
  window.open(route.href, '_blank', 'noopener,noreferrer');
}

function looksLikeUrl(value: string): boolean {
  const v = value.trim();
  if (!v) return false;
  if (v.startsWith('http://') || v.startsWith('https://')) return true;
  if (v.startsWith('//')) return true;
  if (v.startsWith('/')) return true;
  return false;
}

function normalizeHeaderKey(value: string): string {
  return value
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');
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
  importError.value = '';
  importSummary.value = null;
  importResults.value = [];
  tableHeaders.value = [];
  headerKeys.value = [];
  tableRows.value = [];
  importRows.value = [];
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
    headerKeys.value = tableHeaders.value.map((header) => normalizeHeaderKey(header));
    tableRows.value = rows.slice(1).map((row) => {
      const cells = Array.isArray(row) ? row : [];
      return Array.from({ length: columnCount }).map((_, idx) => normalizeCell(cells[idx]));
    });
    importRows.value = rows.slice(1).map((row) => {
      const cells = Array.isArray(row) ? row : [];
      const payload: ImportProductsRow = {};

      headerKeys.value.forEach((key, idx) => {
        if (!key) return;
        const value = normalizeCell(cells[idx]);
        if (value !== '') {
          payload[key] = value;
        }
      });

      return payload;
    });

    const nameIndex = headerKeys.value.findIndex((key) => ['name', 'product_name', 'title'].includes(key));
    const imageIndex = headerKeys.value.findIndex((key) =>
      ['image_urls', 'image', 'imageurl', 'thumbnail', 'thumb', 'thumb_url', 'photo_url'].includes(key),
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

async function onStartImport() {
  if (!canImport.value) {
    return;
  }

  isImporting.value = true;
  importError.value = '';
  importSummary.value = null;
  importResults.value = [];

  try {
    const rowsWithSourceRow = importRows.value.map((row, index) => ({
      ...row,
      row_number: index + 2,
    }));
    const batches = chunkRows(rowsWithSourceRow, IMPORT_BATCH_SIZE);
    const summary = {
      processed: 0,
      created: 0,
      updated: 0,
      skipped: 0,
      failed: 0,
    };
    const results: NonNullable<NonNullable<ImportProductsResponse['data']>['results']> = [];

    for (const batch of batches) {
      const response = await importProducts(batch);
      const batchSummary = response.data?.summary;
      const batchResults = response.data?.results ?? [];

      if (batchSummary) {
        summary.processed += batchSummary.processed ?? 0;
        summary.created += batchSummary.created ?? 0;
        summary.updated += batchSummary.updated ?? 0;
        summary.skipped += batchSummary.skipped ?? 0;
        summary.failed += batchSummary.failed ?? 0;
      }

      results.push(...batchResults);
    }

    importSummary.value = summary;
    importResults.value = results.slice().sort((a, b) => a.row - b.row);

    snackbar.show({
      message: `Product import completed in ${batches.length} batch${batches.length === 1 ? '' : 'es'}.`,
      color: 'success',
    });
  } catch (err: any) {
    const response = err?.response;
    const responseErrors = response?.data?.errors ?? null;
    if (response?.status === 422 && responseErrors && typeof responseErrors === 'object') {
      const messages: string[] = [];
      for (const [field, value] of Object.entries(responseErrors)) {
        if (Array.isArray(value)) {
          messages.push(`${field}: ${value.map((item) => String(item)).join(', ')}`);
        } else if (value != null) {
          messages.push(`${field}: ${String(value)}`);
        }
      }

      importError.value = messages.join(' | ') || 'Import validation failed.';
      snackbar.show({ message: importError.value, color: 'error' });
      return;
    }

    const message = getErrorMessage(err);
    importError.value = message;
    snackbar.show({ message, color: 'error' });
  } finally {
    isImporting.value = false;
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

.result-link {
  cursor: pointer;
}

.result-link:hover {
  background: rgba(var(--v-theme-primary), 0.06);
}

.changes-table {
  margin-top: 12px;
}
</style>
