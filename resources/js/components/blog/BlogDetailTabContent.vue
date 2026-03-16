<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Blog Content</div>
            <div class="text-body-2 text-medium-emphasis">Update blog short description and detailed content.</div>
          </div>
          <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-form ref="contentFormRef">
          <div class="mb-4">
            <app-field-label label="Short Description" />
            <v-textarea
              v-model="form.short_desc"
              variant="outlined"
              density="comfortable"
              auto-grow
              rows="3"
            />
          </div>

          <div class="mt-6">
            <app-field-label label="Content" />
            <RichText v-model="form.content" />
          </div>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive, ref, watch } from 'vue';
import { update as updateBlog, type BlogDetailResponse } from '@/api/blogs.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));

const props = defineProps<{
  item: BlogDetailResponse | null;
  blogId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const contentFormRef = ref();

const form = reactive({
  short_desc: '',
  content: '',
});

watch(
  () => props.item,
  (item) => {
    form.short_desc = item?.short_desc ? String(item.short_desc) : '';
    form.content = item?.content ? String(item.content) : '';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.blogId ?? '').trim();
  if (!id) return;

  const { valid } = await contentFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateBlog(id, {
      short_desc: form.short_desc.trim(),
      content: form.content,
    });
    
    snackbar.show({
      message: 'Blog content updated successfully',
      color: 'success',
    });
    
    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update blog content',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
