<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Category Description</div>
            <div class="text-body-2 text-medium-emphasis">Update category detailed description.</div>
          </div>
          <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-form ref="descriptionFormRef">
          <div class="mt-4">
            <app-field-label label="Description" />
            <RichText v-model="form.description" />
          </div>
        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive, ref, watch } from 'vue';
import { updateProductCategory, type ProductCategoryDetailResponse } from '@/api/product-categories.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const RichText = defineAsyncComponent(() => import('@/components/RichText.vue'));

const props = defineProps<{
  item: ProductCategoryDetailResponse | null;
  categoryId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const snackbar = useSnackbarStore();
const saving = ref(false);
const descriptionFormRef = ref();

const form = reactive({
  description: '',
});

watch(
  () => props.item,
  (item) => {
    form.description = item?.description ? String(item.description) : '';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.categoryId ?? '').trim();
  if (!id) return;

  const { valid } = await descriptionFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateProductCategory(id, {
      description: form.description,
    });
    
    snackbar.show({
      message: 'Category description updated successfully',
      color: 'success',
    });
    
    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update category description',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>
