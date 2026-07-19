<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
         <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Product Category Overview</div>
            <div class="text-body-2 text-medium-emphasis">Edit product category name, slug, and status.</div>
          </div>
             <v-btn color="primary" variant="flat" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>
        
        <v-form ref="overviewFormRef">
          <v-row>
            <v-col cols="12">
              <div>
                <app-field-label label="Title" />
                <v-text-field
                  v-model="form.title"
                  variant="outlined"
                  density="comfortable"
                  :rules="[v => !!v || 'Title is required']"
                  placeholder="Enter category title"
                />
              </div>
            </v-col>
            <v-col cols="12">
              <div>
                <app-field-label label="Slug" />
                <v-text-field
                  v-model="form.slug"
                  variant="outlined"
                  density="comfortable"
                  :rules="[v => !!v || 'Slug is required']"
                  placeholder="category-slug"
                />
              </div>
            </v-col>
            <v-col cols="12" md="4">
              <div>
                <app-field-label label="Parent Category" />
                <div>
                  <v-select
                    :key="parentSelectKey"
                    v-model="form.parent_id"
                    :items="parentCategoryOptions"
                    item-title="title"
                    item-value="value"
                    variant="outlined"
                    placeholder="Select parent category"
                    persistent-placeholder
                    density="comfortable"
                    clearable
                  />
                </div>
              </div>
              
            </v-col>
            <v-col cols="12" md="4">
              <div>
                <app-field-label label="Status" />
                <div>
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
              
            </v-col>
          </v-row>


        </v-form>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import {
  getProductCategoryLookups,
  updateProductCategory,
  type ProductCategoryDetailResponse,
  type ProductCategoryLookupItem,
} from '@/api/product-categories.api';
import AppFieldLabel from '@/components/shared/AppFieldLabel.vue';
import { useSnackbarStore } from '@/stores/snackbar.store';

const props = defineProps<{
  item: ProductCategoryDetailResponse | null;
  categoryId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const overviewFormRef = ref();
const parentCategories = ref<ProductCategoryLookupItem[]>([]);
const parentCategoriesLoaded = ref(false);
const statusOptions = [
  { label: 'Active', value: '1' },
  { label: 'Inactive', value: '0' },
];

const form = reactive({
  title: '',
  slug: '',
  status: '0',
  parent_id: null as number | null,
});
const snackbar = useSnackbarStore();
const saving = ref(false);

const parentSelectKey = computed(() => (parentCategoriesLoaded.value ? 'parent-select-ready' : 'parent-select-loading'));

const parentCategoryOptions = computed(() => {
  return parentCategories.value.map((category) => ({
    title: category.name,
    value: category.id,
  }));
});

watch(
  () => props.item,
  (item) => {
    form.title = item?.title ? String(item.title) : '';
    form.slug = item?.slug ? String(item.slug) : '';
    form.status = item?.status ? '1' : '0';
    const rawParentId = item?.parent_id as number | string | null | undefined;
    form.parent_id = rawParentId === null || rawParentId === undefined || rawParentId === ''
      ? null
      : Number(rawParentId);
  },
  { immediate: true },
);

async function fetchParentCategories() {
  try {
    const categories = await getProductCategoryLookups();
    const currentId = String(props.categoryId ?? '').trim();
    parentCategories.value = categories.filter((category) => String(category.id ?? '').trim() !== currentId);
  } catch {
    parentCategories.value = [];
  } finally {
    parentCategoriesLoaded.value = true;
  }
}

async function onUpdate() {
  const id = String(props.categoryId ?? '').trim();
  if (!id) return;

  const { valid } = await overviewFormRef.value?.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await updateProductCategory(id, {
      title: form.title.trim(),
      slug: form.slug.trim(),
      status: Number(form.status) === 1,
      parent_id: form.parent_id,
    });
    
    snackbar.show({
      message: 'Product category overview updated successfully',
      color: 'success',
    });
    
    emit('updated');
  } catch (error: any) {
    snackbar.show({
      message: error.response?.data?.message || 'Failed to update product category overview',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}

onMounted(fetchParentCategories);
</script>
