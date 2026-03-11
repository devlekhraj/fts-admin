<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div class="mt-10">
          <app-field-label label="Title" />
          <v-textarea rows="2" auto-grow maxlength="200" v-model="form.title" variant="outlined" density="comfortable" />
        </div>

        <div>
          <app-field-label label="Slug" />
          <v-text-field v-model="form.slug" variant="outlined" density="comfortable" />
        </div>

        <v-row>
          <v-col cols="12" md="8">
            <app-field-label label="Author" />
            <v-text-field v-model="form.author" variant="outlined" density="comfortable" />
          </v-col>
          <v-col cols="12" md="4">
            <app-field-label label="Status" />
            <v-select
              v-model="form.status"
              :items="statusOptions"
              item-title="label"
              item-value="value"
              variant="outlined"
              density="comfortable" />
          </v-col>
        </v-row>

        <div class="d-flex justify-center mt-2">
          <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>
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

const form = reactive({
  title: '',
  slug: '',
  author: '',
  status: '0',
});
const statusOptions = [
  { label: 'Active', value: '1' },
  { label: 'Inactive', value: '0' },
];
const snackbar = useSnackbarStore();
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    form.title = item?.title ? String(item.title) : '';
    form.slug = item?.slug ? String(item.slug) : '';
    form.author = item?.author ? String(item.author) : '';
    form.status = item?.status ? '1' : '0';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.blogId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateBlog(id, {
      title: form.title.trim(),
      slug: form.slug.trim(),
      author: form.author.trim(),
      status: Number(form.status) === 1,
    });
    snackbar.show({ message: 'Blog updated successfully.', color: 'success' });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
