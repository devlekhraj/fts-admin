<template>
    <div class="pa-6">
        <div class="d-flex align-center justify-space-between mb-4">
            <div class="text-body-2 text-medium-emphasis">
                Total variants: {{ variants.length }}
            </div>
        </div>

        <v-table v-if="variants.length" density="comfortable">
            <thead>
                <tr>
                    <th>Variant</th>
                    <th>Attributes</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Images</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="variant in variants" :key="String(variant.id)">
                    <tr>
                        <td class="py-3 variant-col">
                            <div class="d-flex align-center ga-3">
                                <div class="variant-thumb rounded">
                                    <v-img v-if="getVariantPreview(variant)" :src="getVariantPreview(variant)!" cover />
                                    <div v-else class="d-flex align-center justify-center h-100">
                                        <v-icon size="18" color="grey-darken-1">mdi-image-outline</v-icon>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-body-2 font-weight-medium">Variant #{{ variant.id }}</div>
                                    <div class="text-caption text-medium-emphasis">
                                        {{ formatAttributes(variant.attributes) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 attr-col">
                            <div class="text-caption">{{ formatAttributes(variant.attributes) }}</div>
                        </td>
                        <td class="py-3">
                            <div class="text-body-2">{{ formatNPR(variant.price) }}</div>
                        </td>
                        <td class="py-3">
                            <v-chip size="small" label variant="tonal" color="primary">
                                {{ Number(variant.quantity ?? 0) }}
                            </v-chip>
                        </td>
                        <td class="py-3">
                            <v-chip size="small" label variant="tonal" color="info" class="variant-images-chip"
                                @click="toggleExpanded(variant)">
                                {{ getVariantImageCount(variant) }}
                                <v-icon end size="14">{{ isExpanded(variant) ? 'mdi-chevron-up' : 'mdi-chevron-down'
                                    }}</v-icon>
                            </v-chip>
                        </td>
                        <td class="py-3">
                            <div class="d-flex align-center ga-2">
                                <v-btn icon size="x-small" variant="tonal" color="primary"
                                    @click="onViewVariant(variant)">
                                    <v-icon size="16">mdi-eye</v-icon>
                                </v-btn>
                                <v-btn icon size="x-small" variant="tonal" color="error"
                                    @click="onDeleteVariant(variant)">
                                    <v-icon size="16">mdi-delete-outline</v-icon>
                                </v-btn>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="isExpanded(variant)">
                        <td colspan="6" class="variant-expanded-cell">
                            <div v-if="getVariantImageCount(variant)" class="variant-images-grid">
                                <div v-for="image in variant.images" :key="String(image.id)"
                                    class="variant-image-item border rounded">
                                    <div class="variant-image-thumb rounded">
                                        <v-img v-if="image.url" :src="String(image.url)" contain />
                                        <div v-else class="d-flex align-center justify-center h-100">
                                            <v-icon size="16" color="grey-darken-1">mdi-image-off-outline</v-icon>
                                        </div>
                                    </div>
                                    <div class="variant-image-details">
                                        <div class="text-caption text-truncate">
                                            {{ image.title || `Image #${image.id}` }}
                                        </div>
                                        <div class="text-caption text-medium-emphasis">
                                            {{ image.alt_text || '-' }}
                                        </div>
                                        <div class="text-caption text-medium-emphasis">
                                            {{ formatBytes(image.file_size ?? image.size) }} | {{ Number(image.width ??
                                            0) }} x
                                            {{ Number(image.height ?? 0) }}
                                        </div>
                                    </div>
                                    <div class="variant-image-actions">
                                        <v-btn v-if="image.url" :href="String(image.url)" target="_blank"
                                            rel="noopener noreferrer" size="small" variant="tonal" color="primary">
                                            <v-icon start size="16">mdi-pencil-outline</v-icon>
                                            Edit image
                                        </v-btn>
                                        <v-btn v-else size="small" variant="tonal" color="primary" disabled>
                                            <v-icon start size="16">mdi-pencil-outline</v-icon>
                                            Edit image
                                        </v-btn>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-caption text-medium-emphasis">No images for this variant.</div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </v-table>

        <div v-else class="empty-variants-state">
            <v-icon size="42" color="grey-darken-1">mdi-shape-outline</v-icon>
            <div class="text-subtitle-1 font-weight-medium mt-2">No variants found</div>
            <div class="text-body-2 text-medium-emphasis">No variant rows are available for this product.</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { formatNPR } from '@/shared/formatters';
import { type ProductDetailResponse, type ProductFileItem, type ProductVariantItem } from '@/api/products.api';
import { formatBytes } from '@/shared/utils';

const props = defineProps<{
    item: ProductDetailResponse | null;
}>();
const emit = defineEmits<{
    (e: 'view-variant', variant: ProductVariantItem): void;
    (e: 'delete-variant', variant: ProductVariantItem): void;
}>();

const variants = computed<ProductVariantItem[]>(() => {
    return Array.isArray(props.item?.variants) ? props.item.variants : [];
});
const expandedVariantIds = ref<Set<string>>(new Set());

function getVariantImageCount(variant: ProductVariantItem): number {
    return Array.isArray(variant.images) ? variant.images.length : 0;
}

function isExpanded(variant: ProductVariantItem): boolean {
    return expandedVariantIds.value.has(String(variant.id));
}

function toggleExpanded(variant: ProductVariantItem) {
    const id = String(variant.id);
    const next = new Set(expandedVariantIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    expandedVariantIds.value = next;
}

function getVariantPreview(variant: ProductVariantItem): string {
    if (!Array.isArray(variant.images) || !variant.images.length) return '';
    const withUrl = variant.images.find((image: ProductFileItem) => String(image.url ?? '').trim().length > 0);
    return String(withUrl?.url ?? '').trim();
}

function formatAttributes(attributes: ProductVariantItem['attributes']): string {
    if (!attributes || typeof attributes !== 'object' || Array.isArray(attributes)) return '-';

    const parts = Object.entries(attributes)
        .filter(([key]) => String(key).trim().length > 0)
        .map(([key, value]) => `${key}: ${formatAttributeValue(value)}`);

    return parts.length ? parts.join(' | ') : '-';
}

function formatAttributeValue(value: unknown): string {
    if (value === null || value === undefined || value === '') return '-';
    if (typeof value === 'object') {
        try {
            return JSON.stringify(value);
        } catch {
            return '[Object]';
        }
    }
    return String(value);
}

function onViewVariant(variant: ProductVariantItem) {
    emit('view-variant', variant);
}

function onDeleteVariant(variant: ProductVariantItem) {
    emit('delete-variant', variant);
}
</script>

<style scoped>
.variant-thumb {
    width: 56px;
    height: 56px;
    overflow: hidden;
}

.variant-col {
    min-width: 240px;
}

.attr-col {
    min-width: 240px;
    max-width: 360px;
}

.variant-images-chip {
    cursor: pointer;
}

.variant-expanded-cell {
    padding: 14px 16px;
}

.variant-images-grid {
    padding: 40px 10px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

@media (max-width: 1279px) {
    .variant-images-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 599px) {
    .variant-images-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.variant-image-item {
    position: relative;
    min-width: 0;
    gap: 10px;
    border: 1px solid rgb(var(--v-theme-outline-variant));
    border-radius: 8px;
    padding: 20px;
    padding-top: 36px;
}

.variant-image-thumb {
    height: 200px;
    object-fit: contain;
    background: rgb(var(--v-theme-surface));
}

.variant-image-details {
    min-width: 0;
    flex: 1 1 auto;
}

.variant-image-actions {
    position: absolute;
    top: 6px;
    right: 6px;
}

.empty-variants-state {
    min-height: 220px;
    border: 1px dashed rgb(var(--v-theme-outline-variant));
    border-radius: 12px;
    background: rgb(var(--v-theme-surface-variant));
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
}
</style>
