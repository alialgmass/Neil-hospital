<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from '@/components/shared/DataTable.vue';
import SearchBar from '@/components/shared/SearchBar.vue';

interface Booking {
    id: string;
    file_no: string;
    patient_name: string;
    patient_phone?: string;
    dept: string;
    visit_date: string;
    status: string;
    pay_status: string;
    price: number;
    doctor_name?: string;
}

const props = defineProps<{
    bookings: { data: Booking[]; current_page: number; last_page: number; total: number };
    filters: { search?: string; dept?: string; from?: string; to?: string };
}>();

const deptLabels: Record<string, string> = {
    clinic:  'العيادة',
    labs:    'الفحوصات',
    surgery: 'العمليات',
    lasik:   'الليزك',
    laser:   'الليزر',
};

const statusLabels: Record<string, string> = {
    completed: 'مكتمل',
    cancelled: 'ملغي',
};

const payStatusLabels: Record<string, string> = {
    paid:    'مسدد',
    partial: 'جزئي',
    unpaid:  'غير مسدد',
};

const columns = [
    { key: 'file_no',       label: 'رقم الملف' },
    { key: 'patient_name',  label: 'المريض' },
    { key: 'dept',          label: 'القسم' },
    { key: 'doctor_name',   label: 'الطبيب' },
    { key: 'visit_date',    label: 'تاريخ الزيارة', sortable: true },
    { key: 'price',         label: 'المبلغ' },
    { key: 'pay_status',    label: 'حالة السداد' },
    { key: 'status',        label: 'الحالة' },
];

const search    = ref(props.filters.search ?? '');
const deptFilter = ref(props.filters.dept  ?? '');
const fromFilter = ref(props.filters.from  ?? '');
const toFilter   = ref(props.filters.to    ?? '');

function applyFilters() {
    router.get('/archive', {
        search: search.value   || undefined,
        dept:   deptFilter.value || undefined,
        from:   fromFilter.value || undefined,
        to:     toFilter.value   || undefined,
    }, { preserveState: true });
}

function goToPage(page: number) {
    router.get('/archive', {
        search: search.value   || undefined,
        dept:   deptFilter.value || undefined,
        from:   fromFilter.value || undefined,
        to:     toFilter.value   || undefined,
        page,
    }, { preserveState: true });
}
</script>

<template>
    <Head title="الأرشيف الطبي" />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">الأرشيف الطبي</h2>
        <span class="text-sm text-hospital-muted">{{ bookings.total }} سجل</span>
    </div>

    <!-- Filters -->
    <div class="mb-5 flex flex-wrap items-end gap-3 rounded-xl border border-hospital-border bg-hospital-bg p-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">القسم</label>
            <select v-model="deptFilter" class="rounded-lg border border-hospital-border bg-white px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters">
                <option value="">الكل</option>
                <option v-for="(label, key) in deptLabels" :key="key" :value="key">{{ label }}</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">من تاريخ</label>
            <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-white px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">إلى تاريخ</label>
            <input v-model="toFilter" type="date" class="rounded-lg border border-hospital-border bg-white px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-hospital-text-2">بحث</label>
            <SearchBar v-model="search" placeholder="اسم المريض أو رقم الملف..." @update:model-value="applyFilters" />
        </div>
    </div>

    <DataTable
        :columns="columns"
        :rows="bookings.data"
        :current-page="bookings.current_page"
        :last-page="bookings.last_page"
        :total="bookings.total"
        empty-text="لا توجد سجلات في الأرشيف"
        @page="goToPage"
    >
        <template #cell-dept="{ value }">
            <span class="rounded-full bg-hospital-primary/10 px-2 py-0.5 text-xs text-hospital-primary">
                {{ deptLabels[value as string] ?? value }}
            </span>
        </template>

        <template #cell-doctor_name="{ value }">
            <span class="text-sm">{{ value ?? '—' }}</span>
        </template>

        <template #cell-price="{ value }">
            <span class="font-medium">{{ Number(value).toLocaleString('ar-EG') }} ج</span>
        </template>

        <template #cell-pay_status="{ value }">
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium"
                :class="{
                    'bg-green-100 text-green-700': value === 'paid',
                    'bg-yellow-100 text-yellow-700': value === 'partial',
                    'bg-red-100 text-red-700': value === 'unpaid',
                }"
            >
                {{ payStatusLabels[value as string] ?? value }}
            </span>
        </template>

        <template #cell-status="{ value }">
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium"
                :class="{
                    'bg-blue-100 text-blue-700': value === 'completed',
                    'bg-gray-100 text-gray-600': value === 'cancelled',
                }"
            >
                {{ statusLabels[value as string] ?? value }}
            </span>
        </template>
    </DataTable>
</template>
