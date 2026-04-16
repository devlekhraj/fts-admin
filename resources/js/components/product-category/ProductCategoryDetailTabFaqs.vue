<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Category FAQs</div>
            <div class="text-body-2 text-medium-emphasis">FAQs attached to this category.</div>
          </div>
          <v-chip v-if="!loading" size="small" label variant="tonal" color="primary">
            {{ items.length }}
          </v-chip>
        </div>

        <template v-if="loading">
          <v-skeleton-loader
            v-for="n in 5"
            :key="n"
            type="list-item-two-line"
            class="mb-3 rounded-lg"
          />
        </template>

        <v-alert v-else-if="items.length === 0" type="info" variant="tonal" density="comfortable">
          No FAQs found.
        </v-alert>

        <v-list v-else class="faq-list py-0">
          <template v-for="(faq, idx) in items" :key="faq.id">
            <v-list-item class="py-4">
              <template #title>
                <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                  <div class="text-body-1 font-weight-medium">
                    {{ faq.question }}
                  </div>
                  <div class="d-flex align-center flex-wrap ga-2">
                    <v-chip size="small" label class="text-capitalize" variant="tonal" color="primary">
                      Category
                    </v-chip>
                    <v-chip
                      v-if="faq.type_name"
                      size="small"
                      label
                      variant="tonal"
                      color="grey"
                      class="text-truncate"
                      style="max-width: 220px"
                      :title="String(faq.type_name)"
                    >
                      {{ faq.type_name }}
                    </v-chip>
                  </div>
                </div>
              </template>

              <div class="faq-answer text-body-2 text-medium-emphasis mt-2" v-html="faq.answer" />
            </v-list-item>

            <v-divider v-if="idx < items.length - 1" />
          </template>
        </v-list>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { listProductCategoryFaqs, type FaqListItem } from '@/api/faqs.api';
import type { ProductCategoryDetailResponse } from '@/api/product-categories.api';

const props = defineProps<{
  item: ProductCategoryDetailResponse | null;
  categoryId: string | number;
}>();

const items = ref<FaqListItem[]>([]);
const loading = ref(false);
let activeRequestId = 0;

async function fetchFaqs() {
  const id = String(props.categoryId ?? '').trim();
  if (!id) return;

  const requestId = ++activeRequestId;
  loading.value = true;
  try {
    const response = await listProductCategoryFaqs(id, { per_page: -1 });
    const list = Array.isArray(response) ? response : response?.data ?? [];
    if (requestId !== activeRequestId) return;
    items.value = list;
  } finally {
    if (requestId === activeRequestId) {
      loading.value = false;
    }
  }
}

onMounted(fetchFaqs);
watch(
  () => props.categoryId,
  () => fetchFaqs(),
);
</script>

<style scoped>
.faq-list :deep(.v-list-item-title),
.faq-list :deep(.v-list-item-subtitle) {
  opacity: 1;
}

.faq-answer :deep(p) {
  margin: 0 0 10px;
}

.faq-answer :deep(p:last-child) {
  margin-bottom: 0;
}

.faq-answer :deep(a) {
  color: inherit;
  text-decoration: underline;
}
</style>

