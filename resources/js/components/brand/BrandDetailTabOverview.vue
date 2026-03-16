<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Brand Overview</div>
            <div class="text-body-2 text-medium-emphasis">Edit brand name, slug, and status.</div>
          </div>
           <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>
        <v-form ref="overviewFormRef">
          <div class="mt-4">
            <app-field-label label="Title" />
            <v-text-field
              v-model="form.name"
              variant="outlined"
              density="comfortable"
              :rules="[v => !!v || 'Title is required']"
              placeholder="Enter brand name"
            />
          </div>

          <div class="mb-4">
            <app-field-label label="Slug" />
            <v-text-field
              v-model="form.slug"
              variant="outlined"
              density="comfortable"
              :rules="[v => !!v || 'Slug is required']"
              placeholder="brand-slug"
            />
          </div>

          <div>
            <app-field-label label="Status" />
            <div style="max-width: 200px;">
              <v-select
                v-model="form.status"
                :items="statusOptions"
                item-title="label"
                item-value="value"
                variant="outlined"
                density="comfortable"
              />
            </div>
          </div>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { updateBrand, type ProductBrandDetailResponse } from '@/api/products.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
  brandId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const overviewFormRef = ref();
const form = reactive({
  name: '',
  slug: '',
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
    form.name = item?.name ? String(item.name) : '';
    form.slug = item?.slug ? String(item.slug) : '';
    form.status = item?.status ? '1' : '0';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.brandId ?? '').trim();
  if (!id) return;

  const { valid } = await overviewFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateBrand(id, {
      name: form.name.trim(),
      slug: form.slug.trim(),
      status: Number(form.status) === 1,
    });
    
    snackbar.show({
      message: 'Brand overview updated successfully',
      color: 'success',
    });
    
    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update brand overview',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
