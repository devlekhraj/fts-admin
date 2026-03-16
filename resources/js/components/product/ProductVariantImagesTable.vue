<template>
    <div class="container">
        <v-row>
            <v-col cols="12" lg="10" offset-lg="1">
                <div class="py-14">
                    <div class="mb-4">
                        <div>
                            <v-btn
                                variant="flat"
                                color="primary"
                                @click="onAddImage"
                            >
                                <v-icon>mdi-image-plus</v-icon>
                                &nbsp;Add Image
                            </v-btn>
                        </div>
                    </div>
                    <v-table
                        v-if="imageCount"
                        density="comfortable"
                        class="variant-images-table border rounded"
                    >
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Details</th>
                                <th>Primary Image</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="image in variant.images"
                                :key="String(image.id)"
                            >
                                <td class="py-3">
                                    <div class="table-image-preview rounded">
                                        <v-img
                                            v-if="image.url"
                                            :src="String(image.url)"
                                            cover
                                            :title="String(image.url)"
                                        />
                                        <div
                                            v-else
                                            class="d-flex align-center justify-center h-100"
                                        >
                                            <v-icon
                                                size="22"
                                                color="grey-darken-1"
                                                >mdi-image-outline</v-icon
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 details-col">
                                    <div class="text-body-2 font-weight-medium">
                                        File #{{ image.id }}
                                    </div>
                                    <div
                                        class="text-caption text-medium-emphasis"
                                    >
                                        {{ image.alt_text || "-" }}
                                    </div>
                                    <div
                                        class="text-caption text-medium-emphasis mt-1"
                                    >
                                        {{ String(image.size_info ?? '').trim() || '-' }}
                                    </div>
                                </td>
                                <td class="py-3">
                                    <v-chip
                                        size="small"
                                        label
                                        variant="tonal"
                                        :color="image.meta?.is_default ? 'primary' : 'default'"
                                    >
                                        {{ image.meta?.is_default ? 'Yes' : 'No' }}
                                    </v-chip>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-center ga-1">
                                        <v-btn
                                            size="small"
                                            variant="tonal"
                                            color="primary"
                                            @click="onEditFile(image)"
                                        >
                                            <v-icon size="16">mdi-cog</v-icon> Edit Image
                                        </v-btn>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                    <div v-else class="text-caption text-medium-emphasis">
                        No images for this variant.
                    </div>
                </div>
            </v-col>
        </v-row>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { ProductVariantItem } from "@/api/products.api";
import ImageUploadModel from "@/components/media/ImageUploadModel.vue";
import ProductVariantImageEditModal from "@/components/product/ProductVariantImageEditModal.vue";
import { openModal } from "@/shared/modal";

const props = defineProps<{
    variant: ProductVariantItem;
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const imageCount = computed(() => {
    return Array.isArray(props.variant?.images)
        ? props.variant.images.length
        : 0;
});

function onAddImage() {
    openModal(
        ImageUploadModel,
        {
            usage_type: "product_variants",
            usage_id: props.variant?.id ?? null,
            directory: "products",
        },
        {
            title: "Add Product Image",
            size: "lg",
            onSaved: () => {
                emit('updated');
            },
        },
    );
}

function onEditFile(file: any) {
    openModal(
        ProductVariantImageEditModal,
        {
            productId: props.variant?.product_id ?? null,
            variantId: props.variant?.id ?? null,
            file,
        },
        {
            title: 'Edit Variant Image',
            size: 'md',
            onSaved: () => {
                emit('updated');
            },
        },
    );
}
</script>

<style scoped>
.variant-images-table {
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgb(var(--v-theme-outline-variant));
    border-radius: 8px;
}

.table-image-preview {
    width: 140px;
    height: 78px;
    background: rgb(var(--v-theme-surface-variant));
    overflow: hidden;
}

.details-col {
    min-width: 320px;
    max-width: 420px;
    word-break: break-word;
}
</style>
