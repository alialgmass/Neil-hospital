<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, PlusCircle } from 'lucide-vue-next';
import Badge from '@/components/shared/Badge.vue';
import DataTable from '@/components/shared/DataTable.vue';
import Modal from '@/components/shared/Modal.vue';
import SearchBar from '@/components/shared/SearchBar.vue';

interface Supplier { id: string; name: string }
interface InventoryItem {
    id: string;
    name: string;
    code?: string;
    category?: string;
    unit?: string;
    quantity: number;
    min_quantity: number;
    unit_cost: number;
    sell_price: number;
    supplier?: Supplier;
    expiry_date?: string;
    location?: string;
}

const props = defineProps<{
    items: { data: InventoryItem[]; current_page: number; last_page: number; total: number };
    categories: string[];
    lowStockCount: number;
    filters: { search?: string; category?: string; low_stock?: string };
}>();

const columns = [
    { key: 'code',         label: 'الكود' },
    { key: 'name',         label: 'الصنف',       sortable: true },
    { key: 'category',     label: 'الفئة' },
    { key: 'quantity',     label: 'الكمية',      sortable: true },
    { key: 'min_quantity', label: 'الحد الأدنى' },
    { key: 'unit_cost',    label: 'سعر الشراء' },
    { key: 'sell_price',   label: 'سعر البيع' },
    { key: 'expiry_date',  label: 'تاريخ الانتهاء' },
    { key: 'supplier',     label: 'المورد' },
];

const search       = ref(props.filters.search    ?? '');
const catFilter    = ref(props.filters.category  ?? '');
const lowStock     = ref(!!props.filters.low_stock);

function applyFilters() {
    router.get('/inventory', {
        search:    search.value    || undefined,
        category:  catFilter.value || undefined,
        low_stock: lowStock.value  ? '1' : undefined,
    }, { preserveState: true });
}
function goToPage(page: number) {
    router.get('/inventory', { search: search.value || undefined, category: catFilter.value || undefined, low_stock: lowStock.value ? '1' : undefined, page }, { preserveState: true });
}

const showAdd = ref(false);
const form = useForm({
    name:         '',
    code:         '',
    category:     '',
    unit:         '',
    quantity:     0,
    min_quantity: 0,
    unit_cost:    0,
    sell_price:   0,
    supplier_id:  '',
    expiry_date:  '',
    location:     '',
});
function submit() {
    form.post('/inventory', { onSuccess: () => { showAdd.value = false; form.reset(); } });
}

function fmt(n: number) { return Number(n).toLocaleString('ar-EG') + ' ج.م'; }
</script>

<template>
    <Head title="المخزون" />

    <!-- Low stock alert -->
    <div v-if="lowStockCount > 0" class="mb-4 flex items-center gap-2 rounded-xl border border-hospital-warning/30 bg-hospital-warning/10 px-4 py-3 text-hospital-warning">
        <AlertTriangle class="h-5 w-5 flex-shrink-0" />
        <span class="text-sm font-medium">{{ lowStockCount }} صنف وصل للحد الأدنى</span>
        <button class="mr-auto text-xs underline" @click="lowStock = true; applyFilters()">عرض فقط</button>
    </div>

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">المخزون والمستلزمات</h2>
        <div class="flex flex-wrap items-center gap-2">
            <SearchBar v-model="search" placeholder="ابحث بالاسم أو الكود..." @update:model-value="applyFilters" />
            <select v-model="catFilter" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters">
                <option value="">كل الفئات</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <label class="flex items-center gap-1.5 text-sm text-hospital-text cursor-pointer">
                <input v-model="lowStock" type="checkbox" class="rounded" @change="applyFilters" />
                منخفض فقط
            </label>
            <button class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90" @click="showAdd = true">
                <PlusCircle class="h-4 w-4" /> إضافة صنف
            </button>
        </div>
    </div>

    <DataTable :columns="columns" :rows="items.data" :current-page="items.current_page" :last-page="items.last_page" :total="items.total" empty-text="لا توجد أصناف" @page="goToPage">
        <template #cell-quantity="{ value, row }">
            <span :class="(row as InventoryItem).quantity <= (row as InventoryItem).min_quantity && (row as InventoryItem).min_quantity > 0 ? 'text-hospital-danger font-semibold' : ''">
                {{ value }} {{ (row as InventoryItem).unit ?? '' }}
            </span>
        </template>
        <template #cell-unit_cost="{ value }">{{ fmt(Number(value)) }}</template>
        <template #cell-sell_price="{ value }">{{ fmt(Number(value)) }}</template>
        <template #cell-supplier="{ row }">{{ (row as InventoryItem).supplier?.name ?? '—' }}</template>
    </DataTable>

    <!-- Add Modal -->
    <Modal v-model="showAdd" title="إضافة صنف جديد" size="lg">
        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">اسم الصنف</label>
                    <input v-model="form.name" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-hospital-danger">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الكود</label>
                    <input v-model="form.code" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الفئة</label>
                    <input v-model="form.category" type="text" list="cats" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <datalist id="cats">
                        <option v-for="c in categories" :key="c" :value="c" />
                    </datalist>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">وحدة القياس</label>
                    <input v-model="form.unit" type="text" placeholder="قطعة / علبة / زجاجة" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الكمية الابتدائية</label>
                    <input v-model.number="form.quantity" type="number" min="0" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">حد التنبيه (الحد الأدنى)</label>
                    <input v-model.number="form.min_quantity" type="number" min="0" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">سعر الشراء</label>
                    <input v-model.number="form.unit_cost" type="number" min="0" step="0.01" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">سعر البيع</label>
                    <input v-model.number="form.sell_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">تاريخ الانتهاء</label>
                    <input v-model="form.expiry_date" type="date" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">مكان التخزين</label>
                    <input v-model="form.location" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showAdd = false">إلغاء</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60">إضافة</button>
            </div>
        </form>
    </Modal>
</template>
