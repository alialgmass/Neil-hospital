<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, Package, PlusCircle, ShoppingCart, TrendingDown } from 'lucide-vue-next';
import { ref } from 'vue';
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
    totalValue: number;
    openOrdersCount: number;
    filters: { search?: string; category?: string; low_stock?: string };
}>();

const categoryTabs = [
    { label: 'كل الأصناف',       value: '' },
    { label: 'أدوية وقطرات',     value: 'أدوية وقطرات' },
    { label: 'مستلزمات جراحية',  value: 'مستلزمات جراحية' },
    { label: 'معدات وأجهزة',     value: 'معدات وأجهزة' },
    { label: 'مستهلكات',         value: 'مستهلكات' },
];

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

const search    = ref(props.filters.search   ?? '');
const catFilter = ref(props.filters.category ?? '');
const lowStock  = ref(!!props.filters.low_stock);

function applyFilters() {
    router.get('/inventory', {
        search:    search.value    || undefined,
        category:  catFilter.value || undefined,
        low_stock: lowStock.value  ? '1' : undefined,
    }, { preserveState: true });
}
function goToPage(page: number) {
    router.get('/inventory', {
        search:    search.value    || undefined,
        category:  catFilter.value || undefined,
        low_stock: lowStock.value  ? '1' : undefined,
        page,
    }, { preserveState: true });
}
function setTab(val: string) {
    catFilter.value = val;
    lowStock.value  = false;
    applyFilters();
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

    <!-- Stats Row -->
    <div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white">
                <Package class="h-5 w-5" />
            </div>
            <div>
                <p class="text-xs font-medium text-blue-600">إجمالي الأصناف</p>
                <p class="text-2xl font-bold text-blue-700">{{ items.total }}</p>
                <p class="text-xs text-blue-500">صنف مسجل</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-red-100 bg-red-50 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-500 text-white">
                <TrendingDown class="h-5 w-5" />
            </div>
            <div>
                <p class="text-xs font-medium text-red-600">أصناف منخفضة</p>
                <p class="text-2xl font-bold text-red-700">{{ lowStockCount }}</p>
                <p class="text-xs text-red-500">تحتاج طلب</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-green-100 bg-green-50 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-600 text-white">
                <span class="text-xs font-bold">ج</span>
            </div>
            <div>
                <p class="text-xs font-medium text-green-600">إجمالي قيمة المخزون</p>
                <p class="text-xl font-bold text-green-700">{{ totalValue.toLocaleString('ar-EG', { maximumFractionDigits: 0 }) }}</p>
                <p class="text-xs text-green-500">جنيه</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-orange-100 bg-orange-50 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-500 text-white">
                <ShoppingCart class="h-5 w-5" />
            </div>
            <div>
                <p class="text-xs font-medium text-orange-600">طلبات توريد مفتوحة</p>
                <p class="text-2xl font-bold text-orange-700">{{ openOrdersCount }}</p>
                <p class="text-xs text-orange-500">جارية</p>
            </div>
        </div>
    </div>

    <!-- Low stock alert -->
    <div v-if="lowStockCount > 0" class="mb-4 flex items-center gap-2 rounded-xl border border-hospital-warning/30 bg-hospital-warning/10 px-4 py-3 text-hospital-warning">
        <AlertTriangle class="h-5 w-5 flex-shrink-0" />
        <span class="text-sm font-medium">{{ lowStockCount }} صنف وصل للحد الأدنى</span>
        <button class="mr-auto text-xs underline" @click="lowStock = true; applyFilters()">عرض فقط</button>
    </div>

    <!-- Category Tabs -->
    <div class="mb-4 flex gap-1 overflow-x-auto border-b border-hospital-border">
        <button
            v-for="tab in categoryTabs"
            :key="tab.value"
            class="whitespace-nowrap px-4 py-2 text-sm font-medium transition-colors"
            :class="catFilter === tab.value && !lowStock
                ? 'border-b-2 border-hospital-primary text-hospital-primary'
                : 'text-hospital-muted hover:text-hospital-text'"
            @click="setTab(tab.value)"
        >
            {{ tab.label }}
        </button>
    </div>

    <!-- Toolbar -->
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <SearchBar v-model="search" placeholder="ابحث بالاسم أو الكود..." @update:model-value="applyFilters" />
            <label class="flex cursor-pointer items-center gap-1.5 text-sm text-hospital-text">
                <input v-model="lowStock" type="checkbox" class="rounded" @change="applyFilters" />
                منخفض فقط
            </label>
        </div>
        <button class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90" @click="showAdd = true">
            <PlusCircle class="h-4 w-4" /> صنف جديد
        </button>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-xl border border-hospital-border shadow-sm">
        <div class="flex items-center justify-between border-b border-hospital-border bg-hospital-bg px-4 py-3">
            <div>
                <p class="text-sm font-bold text-hospital-text">
                    {{ categoryTabs.find(t => t.value === catFilter)?.label ?? 'كل الأصناف' }}
                </p>
                <p class="text-xs text-hospital-muted">{{ items.total }} صنف</p>
            </div>
        </div>
        <DataTable
            :columns="columns"
            :rows="items.data"
            :current-page="items.current_page"
            :last-page="items.last_page"
            :total="items.total"
            empty-text="لا توجد أصناف"
            class="[&>div]:border-none [&>div]:shadow-none [&>div]:rounded-none"
            @page="goToPage"
        >
            <template #cell-quantity="{ value, row }">
                <span :class="(row as InventoryItem).quantity <= (row as InventoryItem).min_quantity && (row as InventoryItem).min_quantity > 0 ? 'text-hospital-danger font-semibold' : ''">
                    {{ value }} {{ (row as InventoryItem).unit ?? '' }}
                </span>
            </template>
            <template #cell-unit_cost="{ value }">{{ fmt(Number(value)) }}</template>
            <template #cell-sell_price="{ value }">{{ fmt(Number(value)) }}</template>
            <template #cell-supplier="{ row }">{{ (row as InventoryItem).supplier?.name ?? '—' }}</template>
        </DataTable>
    </div>

    <!-- Add Modal -->
    <Modal v-model="showAdd" title="إضافة صنف جديد" size="lg">
        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">اسم الصنف <span class="text-hospital-danger">*</span></label>
                    <input v-model="form.name" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-hospital-danger">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الكود</label>
                    <input v-model="form.code" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الفئة</label>
                    <select v-model="form.category" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="">— اختر —</option>
                        <option value="أدوية وقطرات">أدوية وقطرات</option>
                        <option value="مستلزمات جراحية">مستلزمات جراحية</option>
                        <option value="معدات وأجهزة">معدات وأجهزة</option>
                        <option value="مستهلكات">مستهلكات</option>
                        <option v-for="c in categories.filter(c => !['أدوية وقطرات','مستلزمات جراحية','معدات وأجهزة','مستهلكات'].includes(c))" :key="c" :value="c">{{ c }}</option>
                    </select>
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
                    <label class="mb-1 block text-sm font-medium">حد التنبيه (الأدنى)</label>
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
