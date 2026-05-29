<template>
  <div class="pa-6">
    <v-row>
      <v-col cols="12" lg="8" offset-lg="2">
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <div class="text-h6">Brand FAQs ({{ items.length }})</div>
            <div class="text-body-2 text-medium-emphasis">FAQs attached to this brand.</div>
          </div>
          <v-btn prepend-icon="mdi-plus" variant="flat" color="primary" @click="addFaq">Add FAQ</v-btn>
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
                    {{ idx + 1 }}. {{ faq.question }}
                  </div>
                  <div class="d-flex align-center flex-wrap ga-2">
                    <v-btn variant="outlined" size="small" color="primary" @click="handleEdit(faq)">Edit</v-btn>
                    <v-btn variant="outlined" size="small" color="error" @click="handleDelete(faq)">Delete</v-btn>
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
import { listBrandFaqs, type FaqListItem } from '@/api/faqs.api';
import type { ProductBrandDetailResponse } from '@/api/products.api';
import { openModal } from '@/shared/modal';
import FaqFormModal from '@/components/faq/FaqFormModal.vue';
import FaqDeleteModal from '@/components/faq/FaqDeleteModal.vue';

const props = defineProps<{
  item: ProductBrandDetailResponse | null;
  brandId: string | number;
}>();

const items = ref<FaqListItem[]>([]);
const loading = ref(false);
let activeRequestId = 0;

async function fetchFaqs() {
  const id = String(props.brandId ?? '').trim();
  if (!id) return;

  const requestId = ++activeRequestId;
  loading.value = true;
  try {
    const response = await listBrandFaqs(id, { per_page: -1 });
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
  () => props.brandId,
  () => fetchFaqs(),
);

function addFaq() {
  openModal(
    FaqFormModal,
    { type: 'brand', type_id: props.brandId },
    {
      title: 'Add FAQ',
      size: 'lg',
      onSaved: () => fetchFaqs(),
    },
  );
}

function handleEdit(faq: FaqListItem) {
  openModal(
    FaqFormModal,
    { type: 'brand', type_id: props.brandId, faq },
    {
      title: 'Edit FAQ',
      size: 'lg',
      onSaved: () => fetchFaqs(),
    },
  );
}

function handleDelete(faq: FaqListItem) {
  openModal(
    FaqDeleteModal,
    { faq },
    {
      title: 'Delete FAQ',
      size: 'sm',
      showHeader: false,
      onSaved: () => fetchFaqs(),
    },
  );
}
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
