<template>
  <v-card-text class="py-6">
    <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
      {{ error }}
    </v-alert>

    <div class="d-flex align-center justify-space-between mb-4">
      <div>
        <div class="text-subtitle-1 font-weight-medium">Reorder brands</div>
        <div class="text-caption text-medium-emphasis">
          Drag items to reorder them, or use the arrows to move a brand.
        </div>
      </div>
      <v-chip size="small" variant="tonal" color="primary">
        {{ brands.length }} brands
      </v-chip>
    </div>

    <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-4" />

    <div v-if="!loading && brands.length === 0" class="py-10 text-center text-medium-emphasis">
      No brands found.
    </div>

    <div v-else ref="listRef" class="brand-order-list">
      <div
        v-for="row in renderedRows"
        :key="row.key"
        class="brand-order-item-wrap"
        :class="{ 'is-placeholder-wrap': row.type === 'placeholder' }"
        :style="row.type === 'placeholder' && dragRowHeight !== null ? { minHeight: `${dragRowHeight}px` } : undefined"
        :data-order-row="true"
        :data-row-type="row.type"
        :data-row-index="row.type === 'category' ? row.originalIndex : undefined"
        :data-placeholder-index="row.type === 'placeholder' ? row.placeholderIndex : undefined">
        <div
          class="brand-order-item"
          :class="{
            'is-dragging': row.type === 'category' && draggingIndex === row.originalIndex,
            'is-placeholder': row.type === 'placeholder',
            'is-floating-source': row.type === 'category' && draggingIndex === row.originalIndex,
          }">
          <template v-if="row.type === 'placeholder'">
            <div class="drag-handle">
              <v-icon size="18">mdi-drag-vertical</v-icon>
            </div>

            <v-avatar size="42" rounded color="grey-lighten-3" class="mr-3">
              <v-img
                v-if="brands[draggingIndex ?? 0]?.logo"
                :src="brands[draggingIndex ?? 0]?.logo"
                :alt="brands[draggingIndex ?? 0]?.name"
                cover />
              <v-icon v-else size="20" color="grey-darken-1">mdi-tag-outline</v-icon>
            </v-avatar>

            <div class="flex-grow-1 min-w-0">
              <div class="d-flex align-center ga-2">
                <div class="font-weight-medium text-truncate">{{ brands[draggingIndex ?? 0]?.name }}</div>
                <v-chip size="x-small" variant="tonal" color="primary">#{{ brands[draggingIndex ?? 0]?.seq_no }}</v-chip>
              </div>
              <div class="text-caption text-medium-emphasis text-truncate">
                {{ brands[draggingIndex ?? 0]?.slug || '-' }}
              </div>
            </div>

            <div class="d-flex align-center ga-1 drag-preview-actions" aria-hidden="true">
              <v-btn icon size="small" variant="text" tabindex="-1">
                <v-icon size="18">mdi-chevron-up</v-icon>
              </v-btn>
              <v-btn icon size="small" variant="text" tabindex="-1">
                <v-icon size="18">mdi-chevron-down</v-icon>
              </v-btn>
            </div>
          </template>

          <template v-else>
            <div
              class="drag-handle"
              @pointerdown.stop.prevent="startDragFromHandle(row.originalIndex, $event)">
              <v-icon size="18">mdi-drag-vertical</v-icon>
            </div>

            <v-avatar size="42" rounded color="grey-lighten-3" class="mr-3">
              <v-img v-if="row.brand.logo" :src="row.brand.logo" :alt="row.brand.name" cover />
              <v-icon v-else size="20" color="grey-darken-1">mdi-tag-outline</v-icon>
            </v-avatar>

            <div class="flex-grow-1 min-w-0">
              <div class="d-flex align-center ga-2">
                <div class="font-weight-medium text-truncate">{{ row.brand.name }}</div>
                <v-chip size="x-small" variant="tonal" color="primary">#{{ row.brand.seq_no }}</v-chip>
              </div>
              <div class="text-caption text-medium-emphasis text-truncate">
                {{ row.brand.slug || '-' }}
              </div>
            </div>

            <div class="d-flex align-center ga-1">
              <v-btn
                icon
                size="small"
                variant="text"
                :disabled="row.originalIndex === 0 || saving"
                @click="moveItem(row.originalIndex, row.originalIndex - 1)"
                aria-label="Move up">
                <v-icon size="18">mdi-chevron-up</v-icon>
              </v-btn>
              <v-btn
                icon
                size="small"
                variant="text"
                :disabled="row.originalIndex === brands.length - 1 || saving"
                @click="moveItem(row.originalIndex, row.originalIndex + 1)"
                aria-label="Move down">
                <v-icon size="18">mdi-chevron-down</v-icon>
              </v-btn>
            </div>
          </template>
        </div>
      </div>
      <div
        v-if="dragPreview && draggingIndex !== null"
        class="drag-preview"
        :style="{
          transform: `translate3d(${dragPreview.x}px, ${dragPreview.y}px, 0)`,
          width: `${dragPreview.width}px`,
          height: `${dragPreview.height}px`,
        }">
        <div class="brand-order-item is-drag-preview">
          <div class="drag-handle">
            <v-icon size="18">mdi-drag-vertical</v-icon>
          </div>

          <v-avatar size="42" rounded color="grey-lighten-3" class="mr-3">
            <v-img v-if="brands[draggingIndex]?.logo" :src="brands[draggingIndex]?.logo" :alt="brands[draggingIndex]?.name" cover />
            <v-icon v-else size="20" color="grey-darken-1">mdi-tag-outline</v-icon>
          </v-avatar>

          <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-center ga-2">
              <div class="font-weight-medium text-truncate">{{ brands[draggingIndex]?.name }}</div>
              <v-chip size="x-small" variant="tonal" color="primary">#{{ brands[draggingIndex]?.seq_no }}</v-chip>
            </div>
            <div class="text-caption text-medium-emphasis text-truncate">
              {{ brands[draggingIndex]?.slug || '-' }}
            </div>
          </div>

          <div class="d-flex align-center ga-1 drag-preview-actions" aria-hidden="true">
            <v-btn icon size="small" variant="text" tabindex="-1">
              <v-icon size="18">mdi-chevron-up</v-icon>
            </v-btn>
            <v-btn icon size="small" variant="text" tabindex="-1">
              <v-icon size="18">mdi-chevron-down</v-icon>
            </v-btn>
          </div>
        </div>
      </div>
    </div>
  </v-card-text>

  <v-divider />

  <v-card-actions class="pb-4 px-6">
    <v-spacer />
    <v-btn variant="outlined" color="primary" :disabled="loading || saving" @click="reloadBrands">
      <v-icon start size="16">mdi-refresh</v-icon>
      Reload
    </v-btn>
    <v-btn color="primary" variant="tonal" :loading="saving" :disabled="loading || saving || brands.length === 0" @click="onSave">
      <v-icon start size="16">mdi-content-save-outline</v-icon>
      Save Order
    </v-btn>
  </v-card-actions>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { listBrands, reorderBrands, type ProductBrandListItem } from '@/api/products.api';
import { getErrorMessage } from '@/shared/errors';
import { useSnackbarStore } from '@/stores/snackbar.store';

type OrderedBrand = {
  id: number;
  name: string;
  slug: string;
  logo: string;
  seq_no: number;
};

type RenderRow =
  | {
      key: string;
      type: 'category';
      brand: OrderedBrand;
      originalIndex: number;
    }
  | {
      key: string;
      type: 'placeholder';
      placeholderIndex: number;
    };

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', payload?: unknown): void;
}>();

const brands = ref<OrderedBrand[]>([]);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const draggingIndex = ref<number | null>(null);
const dropIndex = ref<number | null>(null);
const dragRowHeight = ref<number | null>(null);
const dragPointerOffsetY = ref<number>(0);
const dragPreview = ref<{
  x: number;
  y: number;
  width: number;
  height: number;
} | null>(null);
const listRef = ref<HTMLElement | null>(null);
const snackbar = useSnackbarStore();

const renderedRows = computed<RenderRow[]>(() => {
  if (draggingIndex.value === null) {
    return brands.value.map((brand, originalIndex) => ({
      key: `brand-${brand.id}`,
      type: 'category' as const,
      brand,
      originalIndex,
    }));
  }

  const sourceIndex = draggingIndex.value;
  const filteredBrands = brands.value.filter((_, index) => index !== sourceIndex);
  const insertIndex = Math.max(0, Math.min(dropIndex.value ?? filteredBrands.length, filteredBrands.length));
  const rows: RenderRow[] = filteredBrands.map((brand, originalIndex) => {
    const actualIndex = originalIndex >= sourceIndex ? originalIndex + 1 : originalIndex;
    return {
      key: `brand-${brand.id}`,
      type: 'category' as const,
      brand,
      originalIndex: actualIndex,
    };
  });

  rows.splice(insertIndex, 0, {
    key: 'placeholder',
    type: 'placeholder' as const,
    placeholderIndex: insertIndex,
  });

  return rows;
});

function normalizeBrand(item: ProductBrandListItem): OrderedBrand {
  return {
    id: Number(item.id),
    name: String(item.name ?? '-'),
    slug: String(item.slug ?? ''),
    logo: typeof item.logo === 'string' ? item.logo : '',
    seq_no: Number(item.seq_no ?? 0),
  };
}

async function reloadBrands() {
  loading.value = true;
  error.value = '';

  try {
    const response = await listBrands({ per_page: -1 });
    const list = Array.isArray(response) ? response : response?.data ?? [];
    brands.value = list.map((item: ProductBrandListItem) => normalizeBrand(item));
  } catch (err) {
    error.value = getErrorMessage(err);
    snackbar.show({ message: error.value, color: 'error' });
  } finally {
    loading.value = false;
  }
}

function moveItem(fromIndex: number, toIndex: number) {
  if (toIndex < 0 || toIndex >= brands.value.length || fromIndex === toIndex) {
    return;
  }

  const next = [...brands.value];
  const [item] = next.splice(fromIndex, 1);
  next.splice(toIndex, 0, item);
  brands.value = next;
}

function startDragFromHandle(index: number, event: PointerEvent) {
  if (saving.value || loading.value) {
    return;
  }

  draggingIndex.value = index;
  dropIndex.value = index;

  const rowElement = (event.currentTarget as HTMLElement | null)?.closest('.brand-order-item-wrap') as HTMLElement | null;
  if (rowElement) {
    const rect = rowElement.getBoundingClientRect();
    const listRect = listRef.value?.getBoundingClientRect();
    const scrollLeft = listRef.value?.scrollLeft ?? 0;
    const scrollTop = listRef.value?.scrollTop ?? 0;
    dragRowHeight.value = rect.height;
    dragPointerOffsetY.value = event.clientY - rect.top;
    dragPreview.value = {
      x: listRect ? rect.left - listRect.left + scrollLeft : rect.left,
      y: listRect ? rect.top - listRect.top + scrollTop : rect.top,
      width: rect.width,
      height: rect.height,
    };
  }

  window.addEventListener('pointermove', onGlobalPointerMove);
  window.addEventListener('pointerup', onGlobalPointerUp);
  window.addEventListener('pointercancel', onGlobalPointerUp);
  document.body.style.userSelect = 'none';
}

function endDrag() {
  draggingIndex.value = null;
  dropIndex.value = null;
  dragRowHeight.value = null;
  dragPointerOffsetY.value = 0;
  dragPreview.value = null;
  document.body.style.userSelect = '';
  window.removeEventListener('pointermove', onGlobalPointerMove);
  window.removeEventListener('pointerup', onGlobalPointerUp);
  window.removeEventListener('pointercancel', onGlobalPointerUp);
}

function updateDropIndex(clientY: number) {
  if (!listRef.value || draggingIndex.value === null) {
    return;
  }

  const rows = Array.from(listRef.value.querySelectorAll<HTMLElement>('[data-row-type="category"]'));

  for (let index = 0; index < rows.length; index += 1) {
    const row = rows[index];
    const rect = row.getBoundingClientRect();
    const isAboveMidpoint = clientY < rect.top + rect.height / 2;

    if (isAboveMidpoint) {
      dropIndex.value = index;
      return;
    }
  }

  dropIndex.value = rows.length;
}

function onGlobalPointerMove(event: PointerEvent) {
  if (draggingIndex.value === null) {
    return;
  }

  if (dragPreview.value) {
    const listRect = listRef.value?.getBoundingClientRect();
    const scrollTop = listRef.value?.scrollTop ?? 0;
    dragPreview.value = {
      ...dragPreview.value,
      y: listRect ? event.clientY - listRect.top + scrollTop - dragPointerOffsetY.value : event.clientY - dragPointerOffsetY.value,
    };
  }

  updateDropIndex(event.clientY);
}

function onGlobalPointerUp() {
  if (draggingIndex.value === null) {
    endDrag();
    return;
  }

  const fromIndex = draggingIndex.value;
  const next = [...brands.value];
  const [item] = next.splice(fromIndex, 1);
  const insertionIndex = Math.max(0, Math.min(dropIndex.value ?? fromIndex, next.length));
  next.splice(insertionIndex, 0, item);
  brands.value = next;
  endDrag();
}

async function onSave() {
  error.value = '';
  const orderedIds = brands.value.map((brand) => brand.id);

  if (orderedIds.length === 0) {
    error.value = 'At least one brand is required.';
    return;
  }

  saving.value = true;
  try {
    const response = await reorderBrands(orderedIds);
    const message = (response as any)?.data?.message ?? 'Brands reordered successfully.';
    snackbar.show({ message, color: 'success' });
    emit('saved', { brand_ids: orderedIds });
    emit('close');
  } catch (err: any) {
    const response = err?.response;
    const responseErrors = response?.data?.errors ?? null;
    if (response?.status === 422 && responseErrors && typeof responseErrors === 'object') {
      const nextErrors: Record<string, string[]> = {};
      for (const [key, messages] of Object.entries(responseErrors)) {
        if (Array.isArray(messages)) {
          nextErrors[key] = messages.map((item) => String(item));
        } else if (messages != null) {
          nextErrors[key] = [String(messages)];
        }
      }
      error.value = nextErrors.brand_ids?.[0] ?? nextErrors['brand_ids.0']?.[0] ?? 'Failed to reorder brands.';
      snackbar.show({ message: error.value, color: 'error' });
      return;
    }

    error.value = getErrorMessage(err);
    snackbar.show({ message: error.value, color: 'error' });
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  reloadBrands();
});

onBeforeUnmount(() => {
  endDrag();
});
</script>

<style scoped>
.brand-order-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: min(60vh, 640px);
  overflow: auto;
  padding-right: 4px;
  position: relative;
}

.brand-order-item-wrap {
  position: relative;
}

.brand-order-item-wrap.is-placeholder-wrap {
  min-height: 94px;
}

.brand-order-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border: 1px solid rgba(var(--v-border-color), 0.2);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
  cursor: grab;
}

.brand-order-item:hover {
  border-color: rgba(var(--v-theme-primary), 0.35);
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}

.brand-order-item.is-dragging {
  opacity: 0.72;
}

.brand-order-item.is-placeholder {
  border-style: dashed;
  border-width: 2px;
  border-color: rgba(var(--v-theme-primary), 0.45);
  background: rgba(var(--v-theme-primary), 0.06);
  box-shadow: inset 0 0 0 1px rgba(var(--v-theme-primary), 0.08);
}

.brand-order-item.is-floating-source {
  opacity: 0.18;
}

.brand-order-item.is-drag-preview {
  border-color: rgba(var(--v-border-color), 0.2);
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  transform: none;
}

.drag-preview {
  position: absolute;
  z-index: 99999;
  pointer-events: none;
  display: flex;
  opacity: 0.98;
  top: 0;
  left: 0;
  will-change: transform;
}

.drag-preview .brand-order-item {
  width: 100%;
}

.drag-preview-actions {
  visibility: hidden;
  flex-shrink: 0;
}

.drag-handle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  color: rgba(var(--v-theme-on-surface), 0.55);
  cursor: grab;
  flex-shrink: 0;
}

.min-w-0 {
  min-width: 0;
}
</style>
