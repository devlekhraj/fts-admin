<template>
	<v-container fluid>
		<v-card class="elevation-0">
			<v-toolbar v-if="title || $slots.actions" flat>
				<v-card class="w-100" flat>
					<slot name="actions"></slot>
				</v-card>
			</v-toolbar>
	
			<v-data-table-server :headers="normalizedHeaders" :items="items" :items-length="total" :loading="loading"
				:search="searchModel" :page="page" :items-per-page="itemsPerPageState"
				:show-expand="showExpand" v-model:expanded="expandedModel"
				:hide-default-footer="total <= itemsPerPageState"
				@update:options="onUpdateOptions">
				<template v-for="(_, name) in $slots" v-slot:[name]="slotProps">
					<slot :name="name" v-bind="slotProps" />
				</template>
			</v-data-table-server>
		</v-card>
	</v-container>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
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
const route = useRoute();
const storageKey = computed(() => `app-datatable:${String(route.name ?? route.path ?? 'default')}:items-per-page`);

const searchModel = computed({
	get: () => props.search ?? '',
	set: () => undefined,
});

// Keep local expanded state so row expansion works even without parent `v-model:expanded`.
// Also avoid coercing keys to string; Vuetify compares expanded keys against `item-value` strictly.
const expandedState = ref<Array<string | number>>(props.expanded ?? []);
watch(
	() => props.expanded,
	(next) => {
		expandedState.value = next ?? [];
	},
);

watch(
	() => props.itemsPerPage,
	(next) => {
		if (typeof next === 'number' && Number.isFinite(next) && next > 0) {
			itemsPerPageState.value = next;
		}
	},
);

function readStoredItemsPerPage(): number | null {
	if (typeof window === 'undefined') return null;

	const stored = Number(window.localStorage.getItem(storageKey.value) ?? '');
	return Number.isInteger(stored) && stored > 0 ? stored : null;
}

function persistItemsPerPage(value: number) {
	if (typeof window === 'undefined') return;
	if (!Number.isInteger(value) || value <= 0) return;

	window.localStorage.setItem(storageKey.value, String(value));
}

function onUpdateOptions(opts: DataTableOptions) {
	if (typeof opts.itemsPerPage === 'number' && Number.isFinite(opts.itemsPerPage) && opts.itemsPerPage > 0) {
		itemsPerPageState.value = opts.itemsPerPage;
		persistItemsPerPage(opts.itemsPerPage);
	}

	emit('update:options', opts);
}

const restoredItemsPerPage = readStoredItemsPerPage();
const itemsPerPageState = ref(restoredItemsPerPage ?? props.itemsPerPage);

if (restoredItemsPerPage && restoredItemsPerPage !== props.itemsPerPage) {
	emit('update:options', {
		page: props.page ?? 1,
		itemsPerPage: restoredItemsPerPage,
		sortBy: [],
	});
}

const expandedModel = computed({
	get: () => expandedState.value,
	set: (val) => {
		expandedState.value = (val as Array<string | number>) ?? [];
		emit('update:expanded', expandedState.value);
	},
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
