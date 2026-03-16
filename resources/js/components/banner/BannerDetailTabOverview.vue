<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" md="8" offset-md="2" lg="6" offset-lg="3">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Banner Overview</div>
            <div class="text-body-2 text-medium-emphasis">Edit banner name, slug, and status.</div>
          </div>
          <v-btn color="primary" :loading="saving" @click="onUpdate" variant="tonal">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-form ref="overviewFormRef">
          <div class="mb-0">
            <app-field-label label="Name" />
            <v-textarea
              v-model="form.name"
              variant="outlined"
              density="comfortable"
              auto-grow
              rows="2"
              :rules="[rules.required]"
            />
          </div>

          <div class="mb-0">
            <app-field-label label="Slug" />
            <v-text-field
              v-model="form.slug"
              variant="outlined"
              density="comfortable"
              :rules="[rules.required]"
            />
          </div>

          <div class="mb-0">
            <v-row>
              <v-col cols="12" md="4">
                <app-field-label label="Status" />
                <v-select
                  v-model="form.status"
                  :items="statusOptions"
                  item-title="label"
                  item-value="value"
                  variant="outlined"
                  density="comfortable"
                  :rules="[rules.required]"
                />
              </v-col>
            </v-row>
          </div>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { update as updateBanner, type BannerDetailResponse } from '@/api/banners.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: BannerDetailResponse | null;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const overviewFormRef = ref();

const statusOptions = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false },
];

const form = reactive({
  name: '',
  slug: '',
  status: false as boolean,
});

const rules = {
  required: (value: any) => !!value || 'This field is required',
};

watch(
  () => props.item,
  (item) => {
    form.name = String(item?.name ?? '');
    form.slug = String(item?.slug ?? '');
    form.status = Boolean(item?.status);
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.item?.id ?? '').trim();
  if (!id) return;

  const { valid } = await overviewFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateBanner(id, {
      name: form.name.trim(),
      slug: form.slug.trim(),
      status: form.status,
    });
    
    snackbar.show({
      message: 'Banner updated successfully',
      color: 'success',
    });
    
    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update banner',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
