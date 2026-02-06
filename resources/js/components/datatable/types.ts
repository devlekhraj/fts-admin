export type DataTableHeader = {
  title: string;
  key: string;
  sortable?: boolean;
  align?: 'start' | 'center' | 'end';
};

export type DataTableOptions = {
  page: number;
  itemsPerPage: number;
  sortBy: { key: string; order?: 'asc' | 'desc' }[];
  search?: string;
};
