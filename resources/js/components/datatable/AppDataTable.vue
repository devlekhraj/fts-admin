<template>
	<v-card class="elevation-0">
		<v-toolbar v-if="title || $slots.actions" flat>
			<v-card class="w-100" flat>
				<slot name="actions"></slot>
			</v-card>
		</v-toolbar>

	<v-data-table-server :headers="normalizedHeaders" :items="items" :items-length="total" :loading="loading"
		:search="searchModel" :page="page" :items-per-page="itemsPerPage"
		:show-expand="showExpand" v-model:expanded="expandedModel"
		:hide-default-footer="total <= itemsPerPage"
		@update:options="(opts) => $emit('update:options', opts)">
			<template v-for="(_, name) in $slots" v-slot:[name]="slotProps">
				<slot :name="name" v-bind="slotProps" />
			</template>
		</v-data-table-server>
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
	page?: number;
	loading?: boolean;
	search?: string;
	searchable?: boolean;
	itemsPerPage?: number;
	showExpand?: boolean;
	expanded?: Array<string | number>;
};

const props = withDefaults(defineProps<Props>(), {
	page: 1,
	loading: false,
	search: '',
	searchable: true,
	itemsPerPage: 10,
	showExpand: false,
	expanded: () => [],
});

const emit = defineEmits<{ (e: 'update:options', options: DataTableOptions): void; (e: 'update:expanded', expanded: Array<string | number>): void }>();

const searchModel = computed({
	get: () => props.search ?? '',
	set: () => undefined,
});

const expandedModel = computed({
	get: () => props.expanded ?? [],
	set: (val) => emit('update:expanded', val as Array<string | number>),
});

const normalizedHeaders = computed<DataTableHeader[]>(() => {
	return props.headers.map((header) => {
		const minWidth = header.minWidth ?? header.width;
		if (!minWidth) return header;
		const style = `min-width: ${typeof minWidth === 'number' ? `${minWidth}px` : minWidth};`;
		const headerProps = {
			...(header.headerProps ?? {}),
			style: [header.headerProps?.style, style].filter(Boolean).join(' '),
		};
		const cellProps = {
			...(header.cellProps ?? {}),
			style: [header.cellProps?.style, style].filter(Boolean).join(' '),
		};
		return { ...header, headerProps, cellProps };
	});
});
</script>
