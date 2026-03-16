<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn color="primary" :loading="saving" @click="onUpdate">
            <v-icon start size="16">mdi-content-save-outline</v-icon>
            Update
          </v-btn>
        </div>

        <v-textarea
          v-model="form.schema_jsonld"
          label="Schema JSON-LD"
          variant="outlined"
          density="comfortable"
          auto-grow
          rows="12" />
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { update as updateProduct, type ProductDetailResponse } from '@/api/products.api';

const props = defineProps<{
  item: ProductDetailResponse | null;
  productId: string | number;
}>();

const emit = defineEmits<{
  (e: 'updated'): void;
}>();

const form = reactive({
  schema_jsonld: '',
});
const saving = ref(false);

watch(
  () => props.item,
  (item) => {
    form.schema_jsonld = item?.schema_jsonld ? String(item.schema_jsonld) : '';
  },
  { immediate: true },
);

async function onUpdate() {
  const id = String(props.productId ?? '').trim();
  if (!id) return;

  saving.value = true;
  try {
    await updateProduct(id, {
      schema_jsonld: form.schema_jsonld,
    });
    emit('updated');
  } finally {
    saving.value = false;
  }
}
</script>
