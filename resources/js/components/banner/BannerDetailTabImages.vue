<template>
    <div class="pa-6">

        <div class="d-flex align-center justify-space-between mb-4">
            <div>
                <!-- <div class="text-h6">Banner Images</div> -->
                <div class="text-body-2 text-medium-emphasis">
                    Total images: {{ bannerFiles.length }}
                </div>
            </div>
            <v-btn color="primary" variant="tonal" @click="onAddImage">
                <v-icon start size="16">mdi-image-plus</v-icon>
                Add Image
            </v-btn>
        </div>

        <v-table v-if="bannerFiles.length" density="comfortable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Banner Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="file in bannerFiles" :key="String(file.id)">

                    <td class="py-3">
                        <div class="table-image-preview rounded">
                            <v-img v-if="file.url" :src="file.url" cover :title="file.url || undefined" />
                            <div v-else class="d-flex align-center justify-center h-100">
                                <v-icon size="22" color="grey-darken-1">mdi-image-outline</v-icon>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 details-col">
                        <div class="text-body-2" style="font-size: 0.8rem;">{{ file.alt_text || `File #${file.file_id ?? file.id}` }}</div>
                        <div class="d-flex align-center ga-2">
                            <div class="text-caption text-medium-emphasis">Redirect: {{ String(file.meta?.link ?? '').trim() || 'Not available' }}
                            </div>
                            <v-btn v-if="String(file.meta?.link ?? '').trim()"
                                :href="String(file.meta?.link ?? '').trim()" target="_blank" rel="noopener noreferrer"
                                icon size="x-small" variant="tonal" color="primary">
                                <v-icon size="14">mdi-open-in-new</v-icon>
                            </v-btn>
                        </div>
                        <div class="text-caption text-medium-emphasis mt-2">
                            {{ formatBytes(file.file_size ?? file.size) }} | {{ Number(file.width ?? 0) }} x {{
                            Number(file.height ?? 0) }} px
                        </div>

                    </td>

                    <td class="py-3">
                        <v-chip size="small" label variant="tonal"
                            :color="file.meta?.is_active === true ? 'success' : 'warning'">
                            {{ file.meta?.is_active === true ? 'Active' : 'Inactive' }}
                        </v-chip>
                    </td>

                    <td class="py-3 meta-col">
                        <div class="text-caption">
                            <strong>Start Date:</strong> {{ formatLongDate(file.meta?.start_date) ?? '-' }}
                        </div>
                        <div class="text-caption">
                            <strong>End Date:</strong> {{ formatLongDate(file.meta?.end_date) ?? '-' }}
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="d-flex align-center ga-1">
                            <v-btn size="small" variant="tonal" color="primary" @click="onEditFile(file)">
                                <v-icon size="16">mdi-cog</v-icon> Edit Banner
                            </v-btn>
                           
                        </div>
                    </td>
                </tr>
            </tbody>
        </v-table>
        <div v-else class="empty-images-state">
            <v-icon size="42" color="grey-darken-1">mdi-image-off-outline</v-icon>
            <div class="text-subtitle-1 font-weight-medium mt-2">No images found</div>
            <div class="text-body-2 text-medium-emphasis">Add an image to get started.</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { BannerDetailResponse } from '@/api/banners.api';
import { deleteFileUsage } from '@/api/files.api';
import BannerImageEditModel from '@/components/banner/BannerImageEditModel.vue';
import ImageUploadModel from '@/components/media/ImageUploadModel.vue';
import { openModal } from '@/shared/modal';
import { formatBytes, formatLongDate } from '@/shared/utils';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
    item: BannerDetailResponse | null;
}>();
const emit = defineEmits<{
    (e: 'changed'): void;
}>();

const bannerFiles = computed(() => props.item?.files ?? []);
const snackbar = useSnackbarStore();

function onEditFile(file: NonNullable<BannerDetailResponse['files']>[number]) {
    // console.log({file});
    openModal(
        BannerImageEditModel,
        {
            file,
        },
        {
            title: 'Edit Banner Image',
            size: 'md',
            onSaved: () => {
                emit('changed');
            },
        },
    );
}


function onAddImage() {
    openModal(
        ImageUploadModel,
        {
            usage_type: 'banners',
            usage_id: props.item?.id ?? null,
            directory: 'banners',
        },
        {
            title: 'Add Banner Image',
            size: 'lg',
            onSaved: () => {
                emit('changed');
            },
        },
    );
}

</script>

<style scoped>
.table-image-preview {
    width: 140px;
    height: 78px;
    background: rgb(var(--v-theme-surface-variant));
    overflow: hidden;
}

.meta-col {
    min-width: 240px;
}

.specs-col {
    min-width: 190px;
}

.details-col {
    min-width: 400px;
    max-width: 400px;
    word-break: break-word;
}

.empty-images-state {
    min-height: 220px;
    /* border: 1px dashed rgb(var(--v-theme-outline-variant)); */
    /* border-radius: 12px; */
    /* background: rgb(var(--v-theme-surface-variant)); */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
}
</style>
