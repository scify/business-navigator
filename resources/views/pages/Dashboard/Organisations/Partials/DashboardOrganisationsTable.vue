<script setup lang="ts">
import { computed, reactive, defineAsyncComponent } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { Organisation } from '@/scripts/types/ModelTypes';

// Conditionally import VueLiteTable only on the client side to avoid SSR issues
const VueLiteTable = defineAsyncComponent(() => import('vue3-table-lite/ts'));

const props = defineProps<{
    organisations: Organisation[];
}>();

const page = usePage();
const appLocale = page.props.app.locale as string;
console.log('appLocale:', appLocale);

const searchQuery = reactive({ value: '' }); // Reactive search query
const sortedRows = [...props.organisations];
const totalRecordCount = computed(() => filteredRows.value.length);

const filteredRows = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return sortedRows.filter((organisation) => organisation.name.toLowerCase().includes(query));
});

const table = reactive({
    isLoading: false,
    isReSearch: false,
    columns: [
        { label: 'ID', field: 'id', sortable: true, isKey: true, width: '5%' },
        { label: 'Name', field: 'name', sortable: true, width: '35%' },
        {
            label: 'Country',
            field: 'country',
            sortable: true, // need a sorting function
            width: '10%',
            display: (row: Organisation) => row.country?.name || 'N/A',
        },
        {
            label: 'Last Updated',
            field: 'updated_at',
            sortable: true,
            width: '20%',
            display: (row: Organisation) => {
                const date = new Date(row.updated_at);
                return isNaN(date.getTime())
                    ? 'N/A'
                    : date.toLocaleString(appLocale, {
                          year: 'numeric',
                          month: '2-digit',
                          day: '2-digit',
                          hour: '2-digit',
                          minute: '2-digit',
                      });
            },
        },
        {
            label: 'Actions',
            field: 'actions',
            sortable: false,
            width: '20%',
            display: (row: { id: number; slug: string }) => {
                const viewButton = `<a href="/explore/org/${row.slug}" class="btn btn-outline-secondary btn-sm">View</a>`;
                const editButton = `<a href="/dashboard/organisations/${row.id}/edit" class="btn btn-primary btn-sm">Edit</a>`;
                return `<div class="d-flex flex-wrap gap-2">${viewButton}${editButton}</div>`;
            },
        },
    ],
    rows: filteredRows,
    pageSize: 10,
    pageOptions: [
        { value: 10, text: 10 },
        { value: 20, text: 20 },
        { value: 50, text: 50 },
    ],
    totalRecordCount: 0,
    sortable: {
        order: 'name',
        sort: 'asc',
    },
    messages: {
        pagingInfo: 'Showing {0}-{1} of {2}',
        pageSizeChangeLabel: 'Rows:',
        gotoPageLabel: 'Go to page:',
        noDataAvailable: 'No data',
    },
});

// Sort function
const doSearch = (offset: number, limit: number, order: keyof Organisation | 'country', sort: 'asc' | 'desc') => {
    table.isLoading = true;
    if (order === 'country') {
        filteredRows.value.sort((a, b) => {
            const aValue = order === 'country' ? a.country?.name || '' : a[order];
            const bValue = order === 'country' ? b.country?.name || '' : b[order];

            if (aValue < bValue) return sort === 'asc' ? -1 : 1;
            if (aValue > bValue) return sort === 'asc' ? 1 : -1;
            return 0;
        });
        table.rows = filteredRows.value;
    }
    table.sortable.order = order;
    table.sortable.sort = sort;
    table.isLoading = false;
};
</script>

<template>
    <section
        id="section-table"
        class="section-table mt-n5"
    >
        <div class="container-xxl px-4">
            <div class="mb-3">
                <input
                    v-model="searchQuery.value"
                    type="text"
                    class="form-control"
                    placeholder="Search by name"
                />
            </div>

            <VueLiteTable
                :is-static-mode="true"
                :columns="table.columns"
                :rows="filteredRows"
                :sortable="table.sortable"
                :messages="table.messages"
                :total="totalRecordCount"
                :page-size="table.pageSize"
                :page-options="table.pageOptions"
                @do-search="doSearch"
            />
        </div>
    </section>
</template>

<style scoped>
::v-deep(.vtl-card) {
    background: transparent;
}

::v-deep(select) {
    background-color: transparent !important;
}
::v-deep(.vtl-table-hover tr:hover) {
    background: color-mix(in srgb, var(--ilt-yellow), transparent 90%) !important;
}

::v-deep(.vtl-table .vtl-thead .vtl-thead-th) {
    /*noinspection CssUnresolvedCustomProperty*/
    color: var(--bs-black);
    background-color: var(--ilt-blue-gray-300);
    border-color: transparent;
}

::v-deep(.vtl-table td),
::v-deep(.vtl-table tr) {
    border: 1px solid var(--bs-light);
    border-left: none;
    border-right: none;
}

::v-deep(.vtl-paging-info) {
    color: #000;
}

::v-deep(.vtl-paging-count-label),
::v-deep(.vtl-paging-page-label) {
    color: #000;
}
::v-deep(.vtl-paging-count-label) {
    padding-inline: 1rem;
}
::v-deep(.vtl-paging-page-dropdown),
::v-deep(.vtl-paging-page-label) {
    display: none;
}

::v-deep(.vtl-paging-pagination-page-link) {
    border: none;
    color: #000;
}
</style>
