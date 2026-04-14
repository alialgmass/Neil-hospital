<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from '@/components/shared/DataTable.vue';
import SearchBar from '@/components/shared/SearchBar.vue';

interface LogEntry {
    id: number;
    action: string;
    module: string;
    record_id?: string;
    description?: string;
    user_name?: string;
    ip_address?: string;
    created_at: string;
}

const props = defineProps<{
    logs: { data: LogEntry[]; current_page: number; last_page: number; total: number };
    users: { id: number; name: string }[];
    modules: string[];
    filters: { module?: string; user_id?: string; from?: string; to?: string; search?: string };
}>();

const columns = [
    { key: 'created_at', label: 'الوقت',   sortable: true },
    { key: 'user_name',  label: 'المستخدم' },
    { key: 'module',     label: 'الوحدة' },
    { key: 'action',     label: 'الإجراء' },
    { key: 'description',label: 'البيان' },
    { key: 'ip_address', label: 'عنوان IP' },
];

const moduleFilter = ref(props.filters.module   ?? '');
const userFilter   = ref(props.filters.user_id  ?? '');
const fromFilter   = ref(props.filters.from     ?? '');
const toFilter     = ref(props.filters.to       ?? '');
const search       = ref(props.filters.search   ?? '');

function applyFilters() {
    router.get('/activity-log', {
        module:  moduleFilter.value || undefined,
        user_id: userFilter.value   || undefined,
        from:    fromFilter.value   || undefined,
        to:      toFilter.value     || undefined,
        search:  search.value       || undefined,
    }, { preserveState: true });
}
function goToPage(page: number) {
    router.get('/activity-log', { module: moduleFilter.value || undefined, user_id: userFilter.value || undefined, from: fromFilter.value || undefined, to: toFilter.value || undefined, search: search.value || undefined, page }, { preserveState: true });
}
</script>

<template>
    <Head title="سجل النظام" />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">سجل النظام والمراجعة</h2>
    </div>

    <!-- Filters -->
    <div class="mb-5 flex flex-wrap items-end gap-3 rounded-xl border border-hospital-border bg-hospital-bg p-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">الوحدة</label>
            <select v-model="moduleFilter" class="rounded-lg border border-hospital-border bg-white px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters">
                <option value="">الكل</option>
                <option v-for="m in modules" :key="m" :value="m">{{ m }}</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">المستخدم</label>
            <select v-model="userFilter" class="rounded-lg border border-hospital-border bg-white px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters">
                <option value="">الكل</option>
                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">من</label>
            <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-white px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">إلى</label>
            <input v-model="toFilter" type="date" class="rounded-lg border border-hospital-border bg-white px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">بحث</label>
            <SearchBar v-model="search" placeholder="بحث في البيان..." @update:model-value="applyFilters" />
        </div>
    </div>

    <DataTable :columns="columns" :rows="logs.data" :current-page="logs.current_page" :last-page="logs.last_page" :total="logs.total" empty-text="لا توجد سجلات" @page="goToPage">
        <template #cell-created_at="{ value }">
            <span class="font-mono text-xs">{{ (value as string)?.replace('T', ' ').slice(0, 19) }}</span>
        </template>
        <template #cell-description="{ value }">
            <span class="max-w-xs truncate text-xs text-hospital-text-2">{{ value ?? '—' }}</span>
        </template>
    </DataTable>
</template>
