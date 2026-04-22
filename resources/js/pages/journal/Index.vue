<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import DataTable from '@/components/shared/DataTable.vue';

interface Account {
    id: string;
    code: string;
    name: string;
}

interface JournalEntry {
    id: string;
    date: string;
    description: string;
    debit_account: Account;
    credit_account: Account;
    amount: number;
    reference?: string;
    source: string;
    creator?: { name: string };
}

const props = defineProps<{
    entries: {
        data: JournalEntry[];
        current_page: number;
        last_page: number;
        total: number;
    };
    accounts: Account[];
    filters: { from?: string; to?: string; source?: string };
}>();

const columns = [
    { key: 'date',           label: 'التاريخ',  sortable: true },
    { key: 'reference',      label: 'رقم القيد' },
    { key: 'description',    label: 'البيان' },
    { key: 'debit_account',  label: 'مدين' },
    { key: 'credit_account', label: 'دائن' },
    { key: 'amount',         label: 'المبلغ',   sortable: true },
    { key: 'creator',        label: 'المسؤول' },
];

const fromFilter   = ref(props.filters.from   ?? '');
const toFilter     = ref(props.filters.to     ?? '');
const sourceFilter = ref(props.filters.source ?? '');

function applyFilters() {
    router.get('/journal', {
        from:   fromFilter.value   || undefined,
        to:     toFilter.value     || undefined,
        source: sourceFilter.value || undefined,
    }, { preserveState: true });
}
function goToPage(page: number) {
    router.get('/journal', {
        from:   fromFilter.value   || undefined,
        to:     toFilter.value     || undefined,
        source: sourceFilter.value || undefined,
        page,
    }, { preserveState: true });
}

const form = useForm({
    date:              new Date().toISOString().slice(0, 10),
    description:       '',
    debit_account_id:  '',
    credit_account_id: '',
    amount:            '' as string | number,
    reference:         '',
});

function submit() {
    form.post('/journal', { onSuccess: () => { form.reset(); form.date = new Date().toISOString().slice(0, 10); } });
}
function clearForm() {
    form.reset();
    form.date = new Date().toISOString().slice(0, 10);
}

const totalDebit  = computed(() => props.entries.data.reduce((s, e) => s + Number(e.amount), 0));
const totalCredit = computed(() => totalDebit.value);
</script>

<template>
    <Head title="قيود اليومية" />

    <!-- Inline Add Form -->
    <div class="mb-5 overflow-hidden rounded-xl border border-hospital-border shadow-sm">
        <div class="border-b border-hospital-border px-4 py-3" style="background: linear-gradient(135deg, #072E63, #0A4FA6)">
            <p class="text-sm font-bold text-white">+ إضافة قيد يومي جديد</p>
        </div>
        <div class="bg-white p-4">
            <form @submit.prevent="submit">
                <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-hospital-muted">رقم القيد</label>
                        <input
                            v-model="form.reference"
                            type="text"
                            placeholder="تلقائي"
                            class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-hospital-muted">التاريخ <span class="text-hospital-danger">*</span></label>
                        <input
                            v-model="form.date"
                            type="date"
                            class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-hospital-muted">الحساب المدين <span class="text-hospital-danger">*</span></label>
                        <select
                            v-model="form.debit_account_id"
                            class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                            :class="{ 'border-hospital-danger': form.errors.debit_account_id }"
                        >
                            <option value="">— اختر —</option>
                            <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} — {{ a.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-hospital-muted">الحساب الدائن <span class="text-hospital-danger">*</span></label>
                        <select
                            v-model="form.credit_account_id"
                            class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                            :class="{ 'border-hospital-danger': form.errors.credit_account_id }"
                        >
                            <option value="">— اختر —</option>
                            <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} — {{ a.name }}</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-xs font-bold text-hospital-muted">البيان <span class="text-hospital-danger">*</span></label>
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="وصف القيد..."
                            class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                            :class="{ 'border-hospital-danger': form.errors.description }"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-xs text-hospital-danger">{{ form.errors.description }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-blue-600">مبلغ المدين (ج) <span class="text-hospital-danger">*</span></label>
                        <input
                            v-model.number="form.amount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full rounded-lg border border-blue-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                            :class="{ 'border-hospital-danger': form.errors.amount }"
                        />
                        <p v-if="form.errors.amount" class="mt-1 text-xs text-hospital-danger">{{ form.errors.amount }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-hospital-danger">مبلغ الدائن (ج) <span class="text-hospital-danger">*</span></label>
                        <input
                            :value="form.amount"
                            type="number"
                            placeholder="0.00"
                            readonly
                            class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-hospital-text-2"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="clearForm">مسح</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-hospital-primary px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-hospital-primary/90 disabled:opacity-60">
                        💾 حفظ القيد
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-hospital-muted">التاريخ</label>
            <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-hospital-muted">إلى</label>
            <input v-model="toFilter" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-hospital-muted">فلتر</label>
            <select v-model="sourceFilter" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters">
                <option value="">كل المصادر</option>
                <option value="manual">يدوية</option>
                <option value="booking">حجوزات</option>
                <option value="purchase">مشتريات</option>
            </select>
        </div>
        <button class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-semibold text-white" @click="applyFilters">🔍 عرض</button>
        <button class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="() => window.print()">🖨️ طباعة</button>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-hospital-border shadow-sm">
        <div class="flex items-center justify-between border-b border-hospital-border bg-hospital-bg px-4 py-3">
            <p class="text-sm font-bold text-hospital-text">قيود اليومية</p>
            <p class="text-xs text-hospital-muted">{{ entries.total }} قيد</p>
        </div>
        <DataTable
            :columns="columns"
            :rows="entries.data"
            :current-page="entries.current_page"
            :last-page="entries.last_page"
            :total="entries.total"
            empty-text="لا توجد قيود"
            class="[&>div]:border-none [&>div]:shadow-none [&>div]:rounded-none"
            @page="goToPage"
        >
            <template #cell-debit_account="{ row }">
                <span class="text-sm">{{ (row as JournalEntry).debit_account?.code }} — {{ (row as JournalEntry).debit_account?.name }}</span>
            </template>
            <template #cell-credit_account="{ row }">
                <span class="text-sm">{{ (row as JournalEntry).credit_account?.code }} — {{ (row as JournalEntry).credit_account?.name }}</span>
            </template>
            <template #cell-amount="{ value }">
                <span class="font-mono">{{ Number(value).toLocaleString('ar-EG') }} ج.م</span>
            </template>
            <template #cell-creator="{ row }">
                {{ (row as JournalEntry).creator?.name ?? '—' }}
            </template>
        </DataTable>

        <!-- Totals Bar -->
        <div class="flex gap-6 rounded-b-xl px-4 py-3 text-sm font-bold text-white" style="background: linear-gradient(135deg, #072E63, #0A4FA6)">
            <span>إجمالي المدين: {{ totalDebit.toLocaleString('ar-EG') }} ج.م</span>
            <span>إجمالي الدائن: {{ totalCredit.toLocaleString('ar-EG') }} ج.م</span>
        </div>
    </div>
</template>
