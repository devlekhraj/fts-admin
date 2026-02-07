<template>
	<v-card class="elevation-0">
		<v-toolbar v-if="title || $slots.actions" flat>
			<v-toolbar-title v-if="title">{{ title }}</v-toolbar-title>
			<v-spacer />
			<v-text-field v-if="searchable" v-model="searchModel" density="compact" variant="outlined" label="Search"
				hide-details class="mr-4" style="max-width: 240px" />
			<slot name="actions" />
		</v-toolbar>

		<v-data-table-server :headers="normalizedHeaders" :items="items" :items-length="total" :loading="loading"
			:search="searchModel" :items-per-page="itemsPerPage"
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
	loading?: boolean;
	search?: string;
	searchable?: boolean;
	itemsPerPage?: number;
};

const props = withDefaults(defineProps<Props>(), {
	loading: false,
	search: '',
	searchable: true,
	itemsPerPage: 10,
});

defineEmits<{ (e: 'update:options', options: DataTableOptions): void }>();

const searchModel = computed({
	get: () => props.search ?? '',
	set: () => undefined,
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
