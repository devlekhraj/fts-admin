<template>
  <div class="pa-6">
    <div class="seo-form-wrap">
      <div class="d-flex align-center justify-space-between mb-4">
        <div>
          <div class="text-h6">Banner SEO</div>
          <div class="text-body-2 text-medium-emphasis">Manage SEO metadata for this banner page.</div>
        </div>
        <v-btn color="primary" variant="tonal" @click="onUpdateSeo">
          <v-icon start size="16">mdi-content-save-outline</v-icon>
          Update
        </v-btn>
      </div>

      <v-form ref="seoFormRef">
        <div class="d-flex flex-column ga-4">
        <div>
          <v-textarea
            v-model="form.metaTitle"
            label="Meta Title"
            :rules="[requiredRule]"
            rows="2"
            auto-grow
            variant="outlined"
            density="comfortable" />
        </div>
          <div>
            <v-textarea
              v-model="form.metaDescription"
              label="Meta Description"
              :rules="[requiredRule]"
              rows="4"
              auto-grow
              variant="outlined"
              density="comfortable" />
          </div>
        <div>
          <v-textarea
            v-model="form.metaKeyword"
            label="Meta Keyword"
            :rules="[requiredRule]"
            rows="2"
            auto-grow
            hint="Separate keywords by comma"
            persistent-hint
            variant="outlined"
            density="comfortable" />
          </div>
          <div>
            <v-text-field :model-value="bannerUrl || '-'" label="Canonical URL" readonly variant="outlined" density="comfortable" />
          </div>
        </div>
      </v-form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import type { BannerDetailResponse } from '@/api/banners.api';

const props = defineProps<{
  item: BannerDetailResponse | null;
}>();

const form = reactive({
  metaTitle: '',
  metaDescription: '',
  metaKeyword: '',
});
const seoFormRef = ref();
const requiredRule = (value: unknown) => (String(value ?? '').trim().length > 0 ? true : 'This field is required.');

const siteOrigin = typeof window !== 'undefined' ? window.location.origin : '';

const bannerUrl = computed(() => {
  const slug = String(props.item?.slug ?? '').trim();
  if (!slug) return '';
  if (/^https?:\/\//i.test(slug)) return slug;
  const normalized = slug.startsWith('/') ? slug : `/${slug}`;
  return `${siteOrigin}${normalized}`;
});

watch(
  () => props.item,
  (item) => {
    form.metaTitle = String(item?.meta_title ?? '');
    form.metaDescription = String(item?.meta_description ?? '');
    form.metaKeyword = String(item?.meta_keyword ?? '');
  },
  { immediate: true },
);

async function onUpdateSeo() {
  const validationResult = await seoFormRef.value?.validate();
  if (!validationResult?.valid) return;

  // TODO: replace with SEO update API call.
  console.log('Update banner SEO:', { ...form });
}
</script>

<style scoped>
.seo-form-wrap {
  max-width: 720px;
  margin: 0 auto;
}
</style>
