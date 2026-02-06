<template>
  <v-card class="elevation-0">
    <v-toolbar v-if="title || $slots.actions" flat>
      <v-toolbar-title v-if="title">{{ title }}</v-toolbar-title>
      <v-spacer />
      <v-text-field
        v-if="searchable"
        v-model="searchModel"
        density="compact"
        variant="outlined"
        label="Search"
        hide-details
        class="mr-4"
        style="max-width: 240px"
      />
      <slot name="actions" />
    </v-toolbar>

    <v-data-table-server
      :headers="headers"
      :items="items"
      :items-length="total"
      :loading="loading"
      :search="searchModel"
      
      @update:options="(opts) => $emit('update:options', opts)"
    />
  </v-card>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { DataTableHeader, DataTableOptions } from './types';

type Props = {
  title?: string;
  headers: DataTableHeader[];
  items: unknown[];
  total: number;
  loading?: boolean;
  search?: string;
  searchable?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  search: '',
  searchable: true,
});

defineEmits<{ (e: 'update:options', options: DataTableOptions): void }>();

const searchModel = computed({
  get: () => props.search ?? '',
  set: () => undefined,
});
</script>
