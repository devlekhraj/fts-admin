<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">SEO Settings</div>
            <div class="text-body-2 text-medium-emphasis">Optimize this blog for search engines.</div>
          </div>
          <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-form ref="seoFormRef">
          <div class="mb-4">
            <app-field-label label="Meta Title" />
            <v-textarea
              v-model="form.meta_title"
              variant="outlined"
              density="comfortable"
              rows="2"
              auto-grow
              persistent-placeholder
              placeholder="Enter SEO title"
            />
          </div>

          <div class="mb-4">
            <app-field-label label="Meta Keywords" />
            <v-textarea
              v-model="form.meta_keywords"
              variant="outlined"
              density="comfortable"
              rows="2"
              auto-grow
              persistent-placeholder
              placeholder="e.g. blog, tech, news"
            />
          </div>

          <div class="mb-4">
            <app-field-label label="Meta Description" />
            <v-textarea
              v-model="form.meta_description"
              variant="outlined"
              density="comfortable"
              rows="4"
              auto-grow
              persistent-placeholder
              placeholder="Brief description for search results"
            />
          </div>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { update as updateBlog, type BlogDetailResponse } from '@/api/blogs.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: BlogDetailResponse | null;
  blogId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const seoFormRef = ref();

const form = reactive({
  meta_title: '',
  meta_keywords: '',
  meta_description: '',
});

watch(
  () => props.item,
  (item) => {
    form.meta_title = item?.meta_title ? String(item.meta_title) : '';
    form.meta_keywords = item?.meta_keywords ? String(item.meta_keywords) : '';
    form.meta_description = item?.meta_description ? String(item.meta_description) : '';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.blogId ?? '').trim();
  if (!id) return;

  const { valid } = await seoFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateBlog(id, {
      meta_title: form.meta_title.trim(),
      meta_keywords: form.meta_keywords.trim(),
      meta_description: form.meta_description.trim(),
    });

    snackbar.show({
      message: 'SEO settings updated successfully',
      color: 'success',
    });

    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update SEO settings',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
