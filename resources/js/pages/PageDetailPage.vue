<template>
  <AppPageHeader title="Page Detail" subtitle="View page information">
    <template #actions>
      <v-btn variant="tonal" color="primary" @click="goBack">
        <v-icon start>mdi-arrow-left</v-icon>
        Back
      </v-btn>
    </template>
  </AppPageHeader>

<v-card class="pa-6">
    <div class="top-grid">
      <div>
        <v-row>
          <v-col cols="12" md="8">
            <div>
              <div class="mb-4">
                <v-text-field label="Title" variant="outlined" density="comfortable" v-model="form.title"/>

              </div>
             
              <div>
                <RichText v-model="form.content" />
              </div>
            </div>
          </v-col>
          <v-col cols="12" md="4">
            <div>
               <div>
                <v-text-field label="Slug" variant="outlined" density="comfortable" v-model="form.slug"></v-text-field>
              </div>
              <div>
                <v-text-field label="Excerpt" variant="outlined" density="comfortable" v-model="form.excerpt"></v-text-field>

              </div>
              <div>
                <v-text-field label="Meta Title" variant="outlined" density="comfortable" v-model="form.meta_title"></v-text-field>

              </div>
              <div>
                <v-text-field label="Meta Keywords" variant="outlined" density="comfortable" v-model="form.meta_keywords"></v-text-field>

              </div>
              <div>
                <v-text-field label="Meta Description" variant="outlined" density="comfortable" v-model="form.meta_description"></v-text-field>
              </div>
              <div>
                <v-btn
                  variant="flat"
                  block
                  color="primary"
                  size="large"
                  :loading="saving"
                  @click="handleSavePage()"
                >
                  <v-icon>mdi-content-save</v-icon>
                  &nbsp; Update
                </v-btn>
              </div>
            </div>
          </v-col>
        </v-row>
      </div>
    </div>

    <div v-if="loading" class="text-body-2 text-medium-emphasis mt-4">Loading page detail...</div>
  </v-card>

</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { getPageDetail, updatePage, type PageDetailResponse } from '@/api/pages.api';
import { formatLongDate } from '@/shared/utils';
import RichText from '@/components/RichText.vue';
import AppTextField from '@/components/shared/AppTextField.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const saving = ref(false);
const pageDetail = ref<PageDetailResponse | null>(null);
const pageId = computed(() => String(route.params.id ?? ''));
const snackbar = useSnackbarStore();

const form = reactive({
  title:'',
  excerpt:'',
  slug:'',
  content:'',
  meta_title:'',
  meta_keywords:'',
  meta_description:'',
})
const prettyMeta = computed(() => {
  if (!pageDetail.value?.meta) return '';
  try {
    return JSON.stringify(pageDetail.value.meta, null, 2);
  } catch {
    return String(pageDetail.value.meta);
  }
});

async function fetchPageDetail() {
  if (!pageId.value) return;
  loading.value = true;
  try {
    pageDetail.value = await getPageDetail(pageId.value);

    const detail = pageDetail.value;
    form.title = detail?.title ? String(detail.title) : '';
    form.slug = detail?.slug ? String(detail.slug) : '';
    form.excerpt = detail?.excerpt ? String(detail.excerpt) : '';
    form.content = detail?.content ? String(detail.content) : '';

    // Prefer root-level meta fields; fall back to meta object if present
    const meta = detail?.meta ?? {};
    form.meta_title = detail?.meta_title
      ? String(detail.meta_title)
      : meta?.meta_title
        ? String(meta.meta_title)
        : '';
    form.meta_keywords = detail?.meta_keywords
      ? String(detail.meta_keywords)
      : meta?.meta_keywords
        ? String(meta.meta_keywords)
        : '';
    form.meta_description = detail?.meta_description
      ? String(detail.meta_description)
      : meta?.meta_description
        ? String(meta.meta_description)
        : '';
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.push({ name: 'admin.pages' });
}

async function handleSavePage(){
  if (!pageId.value) return;
  saving.value = true;
  try {
    await updatePage(pageId.value, { ...form });
    snackbar.show({ message: 'Page updated successfully', color: 'success' });
    await fetchPageDetail();
  } catch (error: any) {
    const message = error?.response?.data?.message || 'Failed to update page';
    snackbar.show({ message, color: 'error' });
  } finally {
    saving.value = false;
  }
}
onMounted(fetchPageDetail);
</script>

<style scoped>
.top-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: center;
}

@media (min-width: 960px) {
  .top-grid {
    grid-template-columns: minmax(0, 1fr);
    gap: 20px;
  }
}

.meta-block {
  background: #f8f9fb;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.9rem;
  white-space: pre-wrap;
}

.content-text {
  white-space: pre-line;
}
</style>
