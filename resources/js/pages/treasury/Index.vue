<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowDownCircle, ArrowUpCircle, PlusCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import DataTable from '@/components/shared/DataTable.vue';
import Modal from '@/components/shared/Modal.vue';
import StatCard from '@/components/shared/StatCard.vue';

interface TreasuryEntry {
    id: string;
    type: 'in' | 'out';
    description: string;
    amount: number;
    date: string;
    reference_no?: string;
    beneficiary?: string;
    source: string;
    account?: { code: string; name: string };
    creator?: { name: string };
}

interface Balance {
    total_in: number;
    total_out: number;
    balance: number;
}

const props = defineProps<{
    entries: {
        data: TreasuryEntry[];
        current_page: number;
        last_page: number;
        total: number;
    };
    balance: Balance;
    filters: { type?: string; from?: string; to?: string };
}>();

const columns = [
    { key: 'date',         label: 'التاريخ',  sortable: true },
    { key: 'type',         label: 'النوع' },
    { key: 'description',  label: 'البيان' },
    { key: 'amount',       label: 'المبلغ',   sortable: true },
    { key: 'beneficiary',  label: 'الجهة' },
    { key: 'source',       label: 'المصدر' },
    { key: 'reference_no', label: 'المرجع' },
    { key: 'creator',      label: 'المسؤول' },
];

const typeFilter = ref(props.filters.type ?? '');
const fromFilter = ref(props.filters.from ?? '');
const toFilter   = ref(props.filters.to   ?? '');

function applyFilters() {
    router.get('/treasury', {
        type: typeFilter.value || undefined,
        from: fromFilter.value || undefined,
        to:   toFilter.value   || undefined,
    }, { preserveState: true });
}
function goToPage(page: number) {
    router.get('/treasury', { type: typeFilter.value || undefined, from: fromFilter.value || undefined, to: toFilter.value || undefined, page }, { preserveState: true });
}

const showAdd = ref(false);
const form = useForm({
    type:         'in' as 'in' | 'out',
    description:  '',
    amount:       '' as string | number,
    date:         new Date().toISOString().slice(0, 10),
    reference_no: '',
    beneficiary:  '',
    account_id:   '',
});
function submit() {
    form.post('/treasury', {
        onSuccess: () => {
 showAdd.value = false; form.reset(); 
},
    });
}

const sourceLabels: Record<string, string> = {
    manual: 'يدوي', booking: 'حجز', payment: 'دفعة', purchase: 'مشتريات',
};
</script>

<template>
    <Head title="الخزنة" />

    <!-- Stats -->
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <StatCard
            label="إجمالي الوارد"
            :value="`${balance.total_in.toLocaleString('ar-EG')} ج.م`"
            color="success"
            :icon="ArrowDownCircle"
        />
        <StatCard
            label="إجمالي الصادر"
            :value="`${balance.total_out.toLocaleString('ar-EG')} ج.م`"
            color="danger"
            :icon="ArrowUpCircle"
        />
        <StatCard
            label="رصيد الخزنة"
            :value="`${balance.balance.toLocaleString('ar-EG')} ج.م`"
            :color="balance.balance >= 0 ? 'primary' : 'danger'"
        />
    </div>

    <!-- Filters + Header -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">حركات الخزنة</h2>
        <div class="flex flex-wrap items-center gap-2">
            <select v-model="typeFilter" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters">
                <option value="">كل الحركات</option>
                <option value="in">وارد</option>
                <option value="out">صادر</option>
            </select>
            <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <input v-model="toFilter"   type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <button class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90 transition-colors" @click="showAdd = true">
                <PlusCircle class="h-4 w-4" /> حركة جديدة
            </button>
        </div>
    </div>

    <DataTable :columns="columns" :rows="entries.data" :current-page="entries.current_page" :last-page="entries.last_page" :total="entries.total" empty-text="لا توجد حركات" @page="goToPage">
        <template #cell-type="{ value }">
            <span :class="value === 'in' ? 'text-hospital-success font-medium' : 'text-hospital-danger font-medium'">
                {{ value === 'in' ? 'وارد ↓' : 'صادر ↑' }}
            </span>
        </template>
        <template #cell-amount="{ value, row }">
            <span :class="(row as TreasuryEntry).type === 'in' ? 'text-hospital-success font-mono' : 'text-hospital-danger font-mono'">
                {{ Number(value).toLocaleString('ar-EG') }} ج.م
            </span>
        </template>
        <template #cell-source="{ value }">
            {{ sourceLabels[value as string] ?? value }}
        </template>
        <template #cell-creator="{ row }">
            {{ (row as TreasuryEntry).creator?.name ?? '—' }}
        </template>
    </DataTable>

    <!-- Add Modal -->
    <Modal v-model="showAdd" title="تسجيل حركة خزنة" size="md">
        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">النوع</label>
                    <select v-model="form.type" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="in">وارد (دخول)</option>
                        <option value="out">صادر (خروج)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">التاريخ</label>
                    <input v-model="form.date" type="date" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">البيان</label>
                    <input v-model="form.description" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-hospital-danger">{{ form.errors.description }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">المبلغ (ج.م)</label>
                    <input v-model.number="form.amount" type="number" min="0.01" step="0.01" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="form.errors.amount" class="mt-1 text-xs text-hospital-danger">{{ form.errors.amount }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الجهة / المستفيد</label>
                    <input v-model="form.beneficiary" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">رقم المرجع (اختياري)</label>
                    <input v-model="form.reference_no" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showAdd = false">إلغاء</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60">تسجيل</button>
            </div>
        </form>
    </Modal>
</template>
