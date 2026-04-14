<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { PlusCircle } from 'lucide-vue-next';
import DataTable from '@/components/shared/DataTable.vue';
import Modal from '@/components/shared/Modal.vue';

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
    filters: { from?: string; to?: string };
}>();

const columns = [
    { key: 'date',           label: 'التاريخ', sortable: true },
    { key: 'description',    label: 'البيان' },
    { key: 'debit_account',  label: 'مدين' },
    { key: 'credit_account', label: 'دائن' },
    { key: 'amount',         label: 'المبلغ', sortable: true },
    { key: 'reference',      label: 'المرجع' },
    { key: 'creator',        label: 'المسؤول' },
];

const fromFilter = ref(props.filters.from ?? '');
const toFilter   = ref(props.filters.to   ?? '');
function applyFilters() {
    router.get('/journal', { from: fromFilter.value || undefined, to: toFilter.value || undefined }, { preserveState: true });
}
function goToPage(page: number) {
    router.get('/journal', { from: fromFilter.value || undefined, to: toFilter.value || undefined, page }, { preserveState: true });
}

const showAdd = ref(false);
const form = useForm({
    date:              new Date().toISOString().slice(0, 10),
    description:       '',
    debit_account_id:  '',
    credit_account_id: '',
    amount:            '' as string | number,
    reference:         '',
});
function submit() {
    form.post('/journal', {
        onSuccess: () => { showAdd.value = false; form.reset(); },
    });
}
</script>

<template>
    <Head title="قيود اليومية" />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">قيود اليومية المحاسبية</h2>
        <div class="flex flex-wrap items-center gap-2">
            <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <input v-model="toFilter"   type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <button class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90" @click="showAdd = true">
                <PlusCircle class="h-4 w-4" /> قيد جديد
            </button>
        </div>
    </div>

    <DataTable :columns="columns" :rows="entries.data" :current-page="entries.current_page" :last-page="entries.last_page" :total="entries.total" empty-text="لا توجد قيود" @page="goToPage">
        <template #cell-debit_account="{ row }">
            <span class="text-sm">
                {{ (row as JournalEntry).debit_account?.code }} — {{ (row as JournalEntry).debit_account?.name }}
            </span>
        </template>
        <template #cell-credit_account="{ row }">
            <span class="text-sm">
                {{ (row as JournalEntry).credit_account?.code }} — {{ (row as JournalEntry).credit_account?.name }}
            </span>
        </template>
        <template #cell-amount="{ value }">
            <span class="font-mono">{{ Number(value).toLocaleString('ar-EG') }} ج.م</span>
        </template>
        <template #cell-creator="{ row }">
            {{ (row as JournalEntry).creator?.name ?? '—' }}
        </template>
    </DataTable>

    <!-- Add Modal -->
    <Modal v-model="showAdd" title="قيد يومية جديد" size="lg">
        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">التاريخ</label>
                    <input v-model="form.date" type="date" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">المرجع</label>
                    <input v-model="form.reference" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">البيان</label>
                    <input v-model="form.description" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-hospital-danger">{{ form.errors.description }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">حساب مدين</label>
                    <select v-model="form.debit_account_id" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="">— اختر —</option>
                        <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} — {{ a.name }}</option>
                    </select>
                    <p v-if="form.errors.debit_account_id" class="mt-1 text-xs text-hospital-danger">{{ form.errors.debit_account_id }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">حساب دائن</label>
                    <select v-model="form.credit_account_id" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="">— اختر —</option>
                        <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} — {{ a.name }}</option>
                    </select>
                    <p v-if="form.errors.credit_account_id" class="mt-1 text-xs text-hospital-danger">{{ form.errors.credit_account_id }}</p>
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">المبلغ (ج.م)</label>
                    <input v-model.number="form.amount" type="number" min="0.01" step="0.01" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="form.errors.amount" class="mt-1 text-xs text-hospital-danger">{{ form.errors.amount }}</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showAdd = false">إلغاء</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60">تسجيل القيد</button>
            </div>
        </form>
    </Modal>
</template>
