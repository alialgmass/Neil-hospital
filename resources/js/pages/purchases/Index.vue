<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { PlusCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Badge from '@/components/shared/Badge.vue';
import DataTable from '@/components/shared/DataTable.vue';
import Modal from '@/components/shared/Modal.vue';

interface Supplier { id: string; name: string }
interface InvoiceItem { item_id?: string; item_name: string; qty: number; unit_cost: number }
interface PurchaseInvoice {
    id: string;
    invoice_no: string;
    supplier?: Supplier;
    invoice_date: string;
    total: number;
    paid_amount: number;
    remaining: number;
    status: 'draft' | 'posted' | 'cancelled';
}

const props = defineProps<{
    invoices: { data: PurchaseInvoice[]; current_page: number; last_page: number; total: number };
    suppliers: Supplier[];
    filters: { from?: string; to?: string };
}>();

const columns = [
    { key: 'invoice_no',   label: 'رقم الفاتورة', sortable: true },
    { key: 'invoice_date', label: 'التاريخ',      sortable: true },
    { key: 'supplier',     label: 'المورد' },
    { key: 'total',        label: 'الإجمالي' },
    { key: 'paid_amount',  label: 'المدفوع' },
    { key: 'remaining',    label: 'المتبقي' },
    { key: 'status',       label: 'الحالة' },
];

const fromFilter = ref(props.filters.from ?? '');
const toFilter   = ref(props.filters.to   ?? '');
function applyFilters() {
 router.get('/purchases', { from: fromFilter.value || undefined, to: toFilter.value || undefined }, { preserveState: true }); 
}
function goToPage(page: number) {
 router.get('/purchases', { from: fromFilter.value || undefined, to: toFilter.value || undefined, page }, { preserveState: true }); 
}

const showAdd = ref(false);
const formData = ref({
    invoice_no:   '',
    supplier_id:  '',
    invoice_date: new Date().toISOString().slice(0, 10),
    discount:     0,
    paid_amount:  0,
    notes:        '',
});
const items = ref<InvoiceItem[]>([{ item_id: '', item_name: '', qty: 1, unit_cost: 0 }]);

const subtotal = computed(() => items.value.reduce((s, i) => s + i.qty * i.unit_cost, 0));
const total    = computed(() => subtotal.value - formData.value.discount);

function addItem() {
 items.value.push({ item_id: '', item_name: '', qty: 1, unit_cost: 0 }); 
}
function removeItem(idx: number) {
 items.value.splice(idx, 1); 
}

function submit() {
    router.post('/purchases', { ...formData.value, items: items.value }, {
        onSuccess: () => {
            showAdd.value = false;
            items.value   = [{ item_id: '', item_name: '', qty: 1, unit_cost: 0 }];
            formData.value.invoice_no = '';
        },
    });
}

const statusLabels: Record<string, string> = { draft: 'مسودة', posted: 'مرحلة', cancelled: 'ملغاة' };
function fmt(n: number) {
 return Number(n).toLocaleString('ar-EG') + ' ج.م'; 
}
</script>

<template>
    <Head title="فواتير الشراء" />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">فواتير الشراء</h2>
        <div class="flex items-center gap-2">
            <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <input v-model="toFilter"   type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <button class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90" @click="showAdd = true">
                <PlusCircle class="h-4 w-4" /> فاتورة جديدة
            </button>
        </div>
    </div>

    <DataTable :columns="columns" :rows="invoices.data" :current-page="invoices.current_page" :last-page="invoices.last_page" :total="invoices.total" empty-text="لا توجد فواتير" @page="goToPage">
        <template #cell-supplier="{ row }">{{ (row as PurchaseInvoice).supplier?.name ?? '—' }}</template>
        <template #cell-total="{ value }"><span class="font-mono">{{ fmt(Number(value)) }}</span></template>
        <template #cell-paid_amount="{ value }"><span class="font-mono text-hospital-success">{{ fmt(Number(value)) }}</span></template>
        <template #cell-remaining="{ value }"><span class="font-mono" :class="Number(value) > 0 ? 'text-hospital-danger' : ''">{{ fmt(Number(value)) }}</span></template>
        <template #cell-status="{ value }">
            <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="value === 'posted' ? 'bg-hospital-success/10 text-hospital-success' : 'bg-hospital-warning/10 text-hospital-warning'">
                {{ statusLabels[value as string] }}
            </span>
        </template>
    </DataTable>

    <!-- Add Invoice Modal -->
    <Modal v-model="showAdd" title="فاتورة مشتريات جديدة" size="xl">
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">رقم الفاتورة</label>
                    <input v-model="formData.invoice_no" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">المورد</label>
                    <select v-model="formData.supplier_id" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="">— بدون مورد —</option>
                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">تاريخ الفاتورة</label>
                    <input v-model="formData.invoice_date" type="date" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
            </div>

            <!-- Items -->
            <div class="space-y-2">
                <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-hospital-text-2">
                    <span class="col-span-5">الصنف</span>
                    <span class="col-span-2">الكمية</span>
                    <span class="col-span-3">سعر الوحدة</span>
                    <span class="col-span-1">الإجمالي</span>
                    <span class="col-span-1" />
                </div>
                <div v-for="(item, idx) in items" :key="idx" class="grid grid-cols-12 items-center gap-2">
                    <input v-model="item.item_name" type="text" placeholder="اسم الصنف" class="col-span-5 rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <input v-model.number="item.qty" type="number" min="0.01" step="0.01" class="col-span-2 rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <input v-model.number="item.unit_cost" type="number" min="0" step="0.01" class="col-span-3 rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <span class="col-span-1 font-mono text-xs">{{ (item.qty * item.unit_cost).toLocaleString('ar-EG') }}</span>
                    <button class="col-span-1 h-8 w-8 rounded-lg text-hospital-danger hover:bg-hospital-danger/10" @click="removeItem(idx)">×</button>
                </div>
                <button class="text-sm text-hospital-primary hover:underline" @click="addItem">+ صنف</button>
            </div>

            <!-- Totals -->
            <div class="grid grid-cols-3 gap-4 border-t border-hospital-border pt-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">الخصم</label>
                    <input v-model.number="formData.discount" type="number" min="0" step="0.01" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">المدفوع</label>
                    <input v-model.number="formData.paid_amount" type="number" min="0" step="0.01" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div class="flex flex-col justify-center rounded-xl bg-hospital-bg p-3">
                    <span class="text-xs text-hospital-text-2">الإجمالي المستحق</span>
                    <span class="font-mono text-lg font-bold text-hospital-primary">{{ total.toLocaleString('ar-EG') }} ج.م</span>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showAdd = false">إلغاء</button>
                <button class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90" @click="submit">تسجيل الفاتورة</button>
            </div>
        </div>
    </Modal>
</template>
