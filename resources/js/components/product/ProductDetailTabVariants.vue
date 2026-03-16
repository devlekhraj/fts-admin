<template>
    <div class="pa-6">
        <div class="d-flex align-center justify-space-between mb-4">
            <div class="text-body-2 text-medium-emphasis">
                Total variants: {{ variants.length }}
            </div>
            <v-btn color="primary" variant="flat" @click="onAddVariant">
                <v-icon start size="16">mdi-plus</v-icon>
                Add Variant
            </v-btn>
        </div>

        <v-table
            v-if="variants.length"
            density="comfortable"
            class="border rounded"
        >
            <thead>
                <tr>
                    <th>Variant</th>
                    <!-- <th>Attributes</th> -->
                    <th>Qty</th>
                    <th>Price</th>
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
                                    <v-img
                                        v-if="getVariantPreview(variant)"
                                        :src="getVariantPreview(variant)!"
                                        cover
                                    />
                                    <div
                                        v-else
                                        class="d-flex align-center justify-center h-100"
                                    >
                                        <v-icon size="18" color="grey-darken-1"
                                            >mdi-image-outline</v-icon
                                        >
                                    </div>
                                </div>
                                <div>
                                    <div class="text-body-2 font-weight-medium">
                                        Variant #{{ variant.id }}
                                    </div>
                                    <div
                                        class="text-caption text-medium-emphasis"
                                    >
                                        {{
                                            formatAttributes(variant.attributes)
                                        }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- <td class="py-3 attr-col">
                            <div class="text-caption">{{ formatAttributes(variant.attributes) }}</div>
                        </td> -->
                        <td class="py-3">
                            <v-chip
                                size="small"
                                label
                                variant="tonal"
                                :color="
                                    Number(variant.quantity ?? 0) > 0
                                        ? 'primary'
                                        : undefined
                                "
                            >
                                {{
                                    Number(variant.quantity ?? 0) === 0
                                        ? "Unavailable"
                                        : `${Number(
                                              variant.quantity ?? 0,
                                          )} Available`
                                }}
                            </v-chip>
                        </td>
                        <td class="py-3">
                            <div class="text-body-2">
                                {{ formatNPR(variant.price) }}
                            </div>
                        </td>
                        <td class="py-3">
                            <v-chip
                                size="small"
                                label
                                variant="tonal"
                                color="info"
                                class="variant-images-chip"
                                @click="toggleExpanded(variant)"
                            >
                                {{ getVariantImageCount(variant) }} images
                                <v-icon end size="14">{{
                                    isExpanded(variant)
                                        ? "mdi-chevron-up"
                                        : "mdi-chevron-down"
                                }}</v-icon>
                            </v-chip>
                        </td>
                        <td class="py-3">
                            <div class="d-flex align-center ga-2">
                                <v-btn
                                    size="small"
                                    variant="tonal"
                                    color="primary"
                                    @click="onEditVariant(variant)"
                                >
                                    <v-icon size="16"
                                        >mdi-square-edit-outline</v-icon
                                    >
                                    Edit
                                </v-btn>
                                <v-btn
                                    size="small"
                                    variant="tonal"
                                    color="error"
                                    @click="onDeleteVariant(variant)"
                                >
                                    <v-icon size="16"
                                        >mdi-trash-can-outline</v-icon
                                    >
                                    Delete
                                </v-btn>
                                <v-btn
                                    size="small"
                                    variant="tonal"
                                    color="primary"
                                    @click="toggleExpanded(variant)"
                                >
                                    <v-icon size="14">{{
                                        isExpanded(variant)
                                            ? "mdi-chevron-up"
                                            : "mdi-chevron-down"
                                    }}</v-icon>
                                    &nbsp; Expand
                                </v-btn>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="isExpanded(variant)">
                        <td colspan="6" class="variant-expanded-cell">
                            <ProductVariantImagesTable :variant="variant" @updated="$emit('updated')" />
                        </td>
                    </tr>
                </template>
            </tbody>
        </v-table>

        <div v-else class="empty-variants-state">
            <v-icon size="42" color="grey-darken-1">mdi-shape-outline</v-icon>
            <div class="text-subtitle-1 font-weight-medium mt-2">
                No variants found
            </div>
            <div class="text-body-2 text-medium-emphasis">
                No variant rows are available for this product.
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { formatNPR } from "@/shared/formatters";
import {
    type ProductDetailResponse,
    type ProductFileItem,
    type ProductVariantItem,
} from "@/api/products.api";
import ProductVariantFormModal from "@/components/product/ProductVariantFormModal.vue";
import ProductVariantImagesTable from "@/components/product/ProductVariantImagesTable.vue";
import { openModal } from "@/shared/modal";

const props = defineProps<{
    item: ProductDetailResponse | null;
    productId: string | number;
}>();
const emit = defineEmits<{
    (e: "view-variant", variant: ProductVariantItem): void;
    (e: "delete-variant", variant: ProductVariantItem): void;
    (e: "updated"): void;
}>();

const variants = computed<ProductVariantItem[]>(() => {
    const itemVariants = props.item?.variants;
    return Array.isArray(itemVariants) ? itemVariants : [];
});
const productAssignedAttributes = computed<Record<string, string>>(() => {
    const raw = props.item?.attributes;
    if (!raw || typeof raw !== "object" || Array.isArray(raw)) return {};

    const productAttributes = (raw as Record<string, unknown>)
        .product_attributes;
    if (
        !productAttributes ||
        typeof productAttributes !== "object" ||
        Array.isArray(productAttributes)
    )
        return {};

    const mapped: Record<string, string> = {};
    Object.entries(productAttributes as Record<string, unknown>).forEach(
        ([key, value]) => {
            const name = String(key ?? "").trim();
            if (!name) return;
            mapped[name] = String(value ?? "").trim();
        },
    );
    return mapped;
});
const variantEnabledAttributes = computed<
    Array<{
        name: string;
        type: string;
        values: string[];
        assigned_value: string;
    }>
>(() => {
    const source =
        props.item?.attribute && typeof props.item.attribute === "object" && !Array.isArray(props.item.attribute)
            ? (props.item.attribute as Record<string, unknown>)
            : props.item?.attributes &&
                typeof props.item.attributes === "object" &&
                !Array.isArray(props.item.attributes)
              ? (props.item.attributes as Record<string, unknown>)
              : null;
    if (!source) return [];

    const attributes = source.attributes;
    if (!Array.isArray(attributes)) return [];

    return attributes
        .filter((row) => {
            const entry = row as Record<string, unknown>;
            return Boolean(entry.use_for_variant);
        })
        .map((row) => {
            const entry = row as Record<string, unknown>;
            const name = String(entry.name ?? "").trim();
            const values = Array.isArray(entry.values)
                ? entry.values
                      .map((value) => String(value ?? "").trim())
                      .filter((value) => value.length > 0)
                : [];

            return {
                name,
                type: String(entry.type ?? "")
                    .trim()
                    .toLowerCase(),
                values,
                assigned_value: name
                    ? String(productAssignedAttributes.value[name] ?? "").trim()
                    : "",
            };
        })
        .filter((entry) => entry.name.length > 0);
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
    if (!Array.isArray(variant.images) || !variant.images.length) return "";
    const withUrl = variant.images.find(
        (image: ProductFileItem) => String(image.url ?? "").trim().length > 0,
    );
    return String(withUrl?.url ?? "").trim();
}

function formatAttributes(
    attributes: ProductVariantItem["attributes"],
): string {
    if (
        !attributes ||
        typeof attributes !== "object" ||
        Array.isArray(attributes)
    )
        return "-";

    const parts = Object.entries(attributes)
        .filter(([key]) => String(key).trim().length > 0)
        .map(([key, value]) => `${key}: ${formatAttributeValue(value)}`);

    return parts.length ? parts.join(" | ") : "-";
}

function formatAttributeValue(value: unknown): string {
    if (value === null || value === undefined || value === "") return "-";
    if (typeof value === "object") {
        try {
            return JSON.stringify(value);
        } catch {
            return "[Object]";
        }
    }
    return String(value);
}

function onEditVariant(variant: ProductVariantItem) {
    openModal(
        ProductVariantFormModal,
        {
            productId: props.productId,
            mode: "edit",
            variant,
            variantAttributes: variantEnabledAttributes.value,
        },
        {
            title: `Edit Variant #${variant.id}`,
            size: "lg",
            onSaved: () => {
                emit("updated");
            },
        },
    );
}

function onAddVariant() {
    openModal(
        ProductVariantFormModal,
        {
            productId: props.productId,
            mode: "create",
            variantAttributes: variantEnabledAttributes.value,
        },
        {
            title: "Add Variant",
            size: "lg",
            onSaved: () => {
                emit("updated");
            },
        },
    );
}

function onDeleteVariant(variant: ProductVariantItem) {
    emit("delete-variant", variant);
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

.empty-variants-state {
    min-height: 220px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
}
</style>
