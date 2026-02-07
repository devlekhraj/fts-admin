export type DataTableHeader = {
  title: string;
  key: string;
  sortable?: boolean;
  align?: 'start' | 'center' | 'end';
  width?: string | number;
  minWidth?: string | number;
  headerProps?: Record<string, unknown>;
  cellProps?: Record<string, unknown>;
};

export type DataTableOptions = {
  page: number;
  itemsPerPage: number;
  sortBy: { key: string; order?: 'asc' | 'desc' }[];
  search?: string;
};
