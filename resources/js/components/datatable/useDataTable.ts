import { ref } from 'vue';
import type { DataTableOptions } from './types';

export function useDataTable() {
  const options = ref<DataTableOptions>({
    page: 1,
    itemsPerPage: 10,
    sortBy: [],
    search: '',
  });

  return { options };
}
