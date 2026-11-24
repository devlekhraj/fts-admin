<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex justify-end mb-4">
          <v-btn v-if="auth.can('edit-product')" color="primary" :loading="saving" @click="onUpdate">
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
          rows="12"
          persistent-placeholder
          :placeholder="placeholder"
          :rules="[jsonRule]"
          hint="Paste only the JSON, without script tags. Leave empty if not needed."
          persistent-hint />
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { update as updateProduct, type ProductDetailResponse } from '@/api/products.api';
import { useAuthStore } from '@/stores/auth.store';

const auth = useAuthStore();

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

const placeholder = '{"@context": "https://schema.org", "@type": "Product"}';

function jsonRule(v: string) {
  const text = String(v ?? '').trim();
  if (!text) return true;
  try {
    const parsed = JSON.parse(text);
    return (parsed && typeof parsed === 'object') || 'Schema must be a JSON object or list';
  } catch {
    return 'Schema must be valid JSON';
  }
}

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
  if (jsonRule(form.schema_jsonld) !== true) return; // the box already shows what is wrong

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
