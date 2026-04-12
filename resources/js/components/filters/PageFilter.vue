<template>
  <v-container fluid class="py-4">
    <v-row align="center">
      <template v-if="hasDefault">
        <v-col cols="12" md="9" lg="8">
          <slot />
        </v-col>
      </template>
      <template v-else>
        <v-col cols="12" md="6" lg="4">
          <slot name="search">
            <div class="d-flex align-center ga-3">
              <AppTextField
                :model-value="search"
                :label="searchLabel"
                :placeholder="searchPlaceholder"
                prepend-inner-icon="mdi-magnify"
                hide-details
                clearable
                :style="{ minWidth: '260px' }"
                density="comfortable"
                variant="outlined"
                @update:model-value="onUpdateSearch"
                @click:clear="emit('clear')"
              />

              <v-btn
                v-if="showSearchButton"
                color="primary"
                variant="tonal"
                height="40"
                @click="emit('search')"
              >
                <v-icon start>mdi-magnify</v-icon>
                Search
              </v-btn>
            </div>
          </slot>
        </v-col>

        <v-col v-if="showCategorySection" cols="12" md="6" lg="3">
          <slot name="category">
            <AppSelectField
              :model-value="categoryModelValue"
              :items="normalizedCategoryItems"
              item-title="title"
              item-value="value"
              :label="categoryLabel"
              clearable
              hide-details
              density="comfortable"
              variant="outlined"
              @update:model-value="onCategoryChange"
            />
          </slot>
        </v-col>

        <v-col v-if="hasExtraLeft" cols="12" md="auto">
          <slot name="extra-left" />
        </v-col>
      </template>

      <v-spacer></v-spacer>

      <v-col v-if="showTotalSection" cols="12" md="auto" class="text-right">
        <slot name="total">
          <div class="text-medium-emphasis">
            <span class="text-primary" style="font-size: smaller;">
              Total: {{ total }} {{ totalLabel }}
            </span>
          </div>
        </slot>
      </v-col>

      <v-col v-if="hasActions" cols="12" md="auto" class="text-right">
        <slot name="actions" />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { computed, useSlots } from 'vue';
import AppTextField from '@/components/shared/AppTextField.vue';
import AppSelectField from '@/components/shared/AppSelectField.vue';

type CategoryOption = { title: string; value: string | number | null } | Record<string, unknown>;

const props = defineProps({
  search: { type: String, default: '' },
  searchLabel: { type: String, default: 'Search' },
  searchPlaceholder: { type: String, default: '' },
  showSearchButton: { type: Boolean, default: true },
  categoryItems: { type: Array as () => CategoryOption[], default: () => [] },
  categoryLabel: { type: String, default: 'Category' },
  categoryModelValue: { type: [String, Number, null], default: null },
  total: { type: Number, default: null },
  totalLabel: { type: String, default: 'Items found.' },
  showTotal: { type: Boolean, default: null },
});

const emit = defineEmits([
  'update:search',
  'update:categoryModelValue',
  'search',
  'clear',
  'category-change',
]);

const slots = useSlots();
const hasDefault = computed(() => Boolean(slots.default));
const hasCategory = computed(() => Array.isArray(props.categoryItems) && props.categoryItems.length > 0);
const showTotal = computed(() => props.showTotal !== null ? props.showTotal : typeof props.total === 'number');
const showCategorySection = computed(() => Boolean(slots.category) || hasCategory.value);
const hasExtraLeft = computed(() => Boolean(slots['extra-left']));
const showTotalSection = computed(() => Boolean(slots.total) || showTotal.value);
const hasActions = computed(() => Boolean(slots.actions));

const normalizedCategoryItems = computed(() => {
  if (!hasCategory.value) return [];
  return props.categoryItems.map((item: any) => ({
    title: item?.title ?? item?.label ?? String(item?.value ?? ''),
    value: item?.value ?? null,
  }));
});

function onUpdateSearch(value: string) {
  emit('update:search', value);
}

function onCategoryChange(value: string | number | null) {
  emit('update:categoryModelValue', value);
  emit('category-change', value);
}
</script>
