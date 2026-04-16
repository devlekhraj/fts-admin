<template>
  <AppPageHeader title="FAQs" subtitle="FAQs Management" />

  <v-card variant="flat" class="pa-0">
    <v-card-text class="pa-0">
      <div class="pa-4 pb-2">
        <v-row dense>
          <v-col cols="12" md="3">
            <v-select
              v-model="selectedType"
              :items="typeSelectItems"
              label="Filter by type"
              density="compact"
              variant="outlined"
              hide-details
              :clearable="false"
            />
          </v-col>
          <v-col v-if="showRelatedFilter" cols="12" md="3">
            <v-select
              v-model="selectedTypeId"
              :items="relatedSelectItems"
              :label="relatedFilterLabel"
              density="compact"
              variant="outlined"
              hide-details
              :clearable="false"
            />
          </v-col>
          <v-col cols="12" :md="showRelatedFilter ? 4 : 6">
            <v-text-field
              v-model="search"
              label="Search FAQs"
              density="compact"
              variant="outlined"
              hide-details
              clearable
              @click:clear="onClearSearch"
            />
          </v-col>
          <v-col cols="12" md="2" class="d-flex align-center">
            <v-btn color="primary" variant="flat" :disabled="searchLoading" @click="onSearch">
              <v-progress-circular
                v-if="searchLoading"
                indeterminate
                size="16"
                width="2"
                class="mr-2"
              />
              <v-icon v-else start>mdi-magnify</v-icon>
              Search
            </v-btn>
          </v-col>
        </v-row>

        <div v-if="!loading" class="text-caption text-medium-emphasis mt-2">
          Showing {{ items.length }} FAQ{{ items.length === 1 ? '' : 's' }}
        </div>
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
                    {{ normalizeType(faq.type) }}
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

            <div class="faq-answer text-body-2 text-medium-emphasis mt-2" v-html="faq.answer"></div>
          </v-list-item>

          <v-divider v-if="idx < items.length - 1" />
        </template>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { listFaqs, type FaqListItem } from '@/api/faqs.api';
import { listProductBrandsLite } from '@/api/product-brands.api';
import { listProductCategoriesLite } from '@/api/product-categories.api';

const items = ref<FaqListItem[]>([]);
const loading = ref(false);
const selectedType = ref<string>('all');
const typeOptions = ref<string[]>(['all', 'general']);
const selectedTypeId = ref<string>('all');
const relatedOptions = ref<Array<{ value: string, title: string }>>([]);
const relatedCounts = ref<Record<string, number>>({});
const search = ref('');
const appliedSearch = ref('');
let activeRequestId = 0;
const searchLoading = ref(false);
let activeSearchRequestId = 0;
let activeRelatedRequestId = 0;
const skipNextTypeIdWatch = ref(false);

const typeSelectItems = computed(() => (
  typeOptions.value.map((value) => ({
    value,
    title: value === 'all' ? 'All' : formatOptionTitle(value),
  }))
));

function normalizeType(value: unknown): string {
  const raw = String(value ?? '').trim();
  return raw.length ? raw : 'general';
}

function formatOptionTitle(value: string): string {
  const trimmed = String(value ?? '').trim();
  if (!trimmed.length) return trimmed;
  if (/[A-Z]/.test(trimmed)) return trimmed;
  return trimmed.replace(/\b([a-z])/g, (match) => match.toUpperCase());
}

const showRelatedFilter = computed(() => selectedType.value === 'brand' || selectedType.value === 'category');

const relatedFilterLabel = computed(() => (
  selectedType.value === 'brand' ? 'Brand' : 'Category'
));

const relatedSelectItems = computed(() => ([
  {
    value: 'all',
    title: `All (${Object.values(relatedCounts.value).reduce((sum, count) => sum + count, 0)})`,
  },
  ...relatedOptions.value.map((option) => ({
    value: option.value,
    title: `${formatOptionTitle(option.title)} (${relatedCounts.value[option.value] ?? 0})`,
  })),
]));

function updateTypeOptions(nextItems: FaqListItem[]) {
  const unique = new Set<string>();
  unique.add('general');

  for (const entry of nextItems) {
    unique.add(normalizeType(entry.type));
  }

  const sorted = Array.from(unique).sort((a, b) => a.localeCompare(b));
  typeOptions.value = ['all', ...sorted];
}

async function fetchFaqs(
  type: string = selectedType.value,
  rawSearch: string = search.value,
  typeId: string = selectedTypeId.value,
  opts?: { searchRequestId?: number },
) {
  const requestId = ++activeRequestId;
  loading.value = true;
  try {
    const trimmedSearch = rawSearch.trim();
    const params: Record<string, unknown> = { per_page: -1 };
    if (type !== 'all') {
      params.type = type;
    }
    if (typeId !== 'all') {
      params.type_id = typeId;
    }
    if (trimmedSearch) {
      params.search = trimmedSearch;
    }

    const response = await listFaqs(params);
    const list = Array.isArray(response) ? response : response?.data ?? [];

    // Ignore outdated responses (typing search / changing filters quickly).
    if (requestId !== activeRequestId) {
      return;
    }

    items.value = list;

    if (type === 'all' && !trimmedSearch) {
      updateTypeOptions(list);
    }
  } finally {
    if (requestId === activeRequestId) {
      loading.value = false;
    }

    if (opts?.searchRequestId && opts.searchRequestId === activeSearchRequestId) {
      searchLoading.value = false;
    }
  }
}

onMounted(() => {
  fetchFaqs('all', '', 'all');
});

async function loadRelatedOptionsForType(type: string) {
  const requestId = ++activeRelatedRequestId;

  if (type === 'brand') {
    const brands = await listProductBrandsLite();
    if (requestId !== activeRelatedRequestId) return;
    relatedOptions.value = brands.map((brand) => ({
      value: String(brand.id),
      title: String(brand.name ?? ''),
    })).filter((entry) => entry.title.length > 0);

    const response = await listFaqs({ per_page: -1, type });
    if (requestId !== activeRelatedRequestId) return;
    const list = Array.isArray(response) ? response : response?.data ?? [];
    const counts: Record<string, number> = {};
    for (const item of list) {
      const key = String((item as any)?.type_id ?? '').trim();
      if (!key.length) continue;
      counts[key] = (counts[key] ?? 0) + 1;
    }
    relatedCounts.value = counts;
    return;
  }

  if (type === 'category') {
    const categories = await listProductCategoriesLite();
    if (requestId !== activeRelatedRequestId) return;
    relatedOptions.value = categories.map((category) => ({
      value: String(category.id),
      title: String(category.title ?? ''),
    })).filter((entry) => entry.title.length > 0);

    const response = await listFaqs({ per_page: -1, type });
    if (requestId !== activeRelatedRequestId) return;
    const list = Array.isArray(response) ? response : response?.data ?? [];
    const counts: Record<string, number> = {};
    for (const item of list) {
      const key = String((item as any)?.type_id ?? '').trim();
      if (!key.length) continue;
      counts[key] = (counts[key] ?? 0) + 1;
    }
    relatedCounts.value = counts;
    return;
  }

  if (requestId !== activeRelatedRequestId) return;
  relatedOptions.value = [];
  relatedCounts.value = {};
}

function onSearch() {
  appliedSearch.value = search.value.trim();
  searchLoading.value = true;
  const searchRequestId = ++activeSearchRequestId;
  fetchFaqs(selectedType.value, appliedSearch.value, selectedTypeId.value, { searchRequestId });
}

function onClearSearch() {
  search.value = '';
  appliedSearch.value = '';
  searchLoading.value = false;
  fetchFaqs(selectedType.value, '', selectedTypeId.value);
}

watch(selectedType, (next) => {
  skipNextTypeIdWatch.value = true;
  selectedTypeId.value = 'all';
  relatedOptions.value = [];
  relatedCounts.value = {};
  loadRelatedOptionsForType(next);
  fetchFaqs(next, appliedSearch.value, 'all');
});

watch(selectedTypeId, (next) => {
  if (skipNextTypeIdWatch.value) {
    skipNextTypeIdWatch.value = false;
    return;
  }
  if (!showRelatedFilter.value) return;
  fetchFaqs(selectedType.value, appliedSearch.value, next);
});
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
