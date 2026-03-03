<template>
  <div class="pa-6 pt-10">
    <v-row>
      <v-col cols="12" lg="6" offset-lg="3">
        <div class="d-flex align-center justify-space-between mb-4">
          <div>
            <div class="text-h6">Banner Overview</div>
            <div class="text-body-2 text-medium-emphasis">Edit banner title, slug, and status.</div>
          </div>
          <v-btn color="primary" variant="tonal" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>
        <v-form ref="overviewFormRef">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.title"
                label="Title"
                :rules="[requiredRule]"
                variant="outlined"
                density="comfortable" />
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.slug"
                label="Slug"
                :rules="[requiredRule]"
                variant="outlined"
                density="comfortable" />
            </v-col>
            <v-col cols="12">
              <v-select
                v-model="form.status"
                label="Status"
                :items="statusItems"
                :rules="[requiredRule]"
                item-title="label"
                item-value="value"
                variant="outlined"
                density="comfortable" />
            </v-col>
          </v-row>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import type { BannerDetailResponse } from '@/api/banners.api';

const props = defineProps<{
  item: BannerDetailResponse | null;
}>();

const statusItems = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false },
];

const form = reactive({
  title: '',
  slug: '',
  status: false as boolean,
});
const overviewFormRef = ref();
const requiredRule = (value: unknown) => (String(value ?? '').trim().length > 0 ? true : 'This field is required.');

watch(
  () => props.item,
  (item) => {
    form.title = String(item?.name ?? '');
    form.slug = String(item?.slug ?? '');
    form.status = Boolean(item?.status);
  },
  { immediate: true },
);

async function onUpdate() {
  const validationResult = await overviewFormRef.value?.validate();
  if (!validationResult?.valid) return;

  // TODO: replace with update API call.
  console.log('Update banner overview:', { ...form });
}
</script>
