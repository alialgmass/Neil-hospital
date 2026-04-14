<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarPlus, ClipboardList, Package } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Badge from '@/components/shared/Badge.vue';
import DataTable from '@/components/shared/DataTable.vue';
import Modal from '@/components/shared/Modal.vue';

interface OrBed {
    id: number;
    label: string;
}

interface Doctor {
    id: string;
    name: string;
}

interface Surgery {
    id: string;
    booking: { file_no: string; patient_name: string };
    procedure: string;
    eye: 'OD' | 'OS' | 'OU' | null;
    anaesthesia: string | null;
    surgeon: Doctor | null;
    or_bed: { id: number; bed_number: string; room: { name: string } } | null;
    status: 'scheduled' | 'prep' | 'in_progress' | 'completed' | 'cancelled';
    scheduled_at: string | null;
    supply_total: number;
}

interface Paginator {
    data: Surgery[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    surgeries: Paginator;
    availableBeds: OrBed[];
    dept: string;
    filters: { status?: string };
}>();

const columns = [
    { key: 'scheduled_at', label: 'الموعد',      sortable: true },
    { key: 'file_no',      label: 'رقم الملف' },
    { key: 'patient',      label: 'المريض' },
    { key: 'procedure',    label: 'الإجراء' },
    { key: 'eye',          label: 'العين' },
    { key: 'surgeon',      label: 'الطبيب' },
    { key: 'or_bed',       label: 'السرير' },
    { key: 'status',       label: 'الحالة' },
    { key: 'supply_total', label: 'تكلفة المستلزمات' },
];

/* ── Filters ── */
const statusFilter = ref(props.filters.status ?? '');
function applyFilters() {
    router.get('/surgery', { status: statusFilter.value || undefined }, { preserveState: true });
}
function goToPage(page: number) {
    router.get('/surgery', { status: statusFilter.value || undefined, page }, { preserveState: true });
}

/* ── Schedule Modal ── */
const showSchedule = ref(false);
const scheduleForm = useForm({
    booking_id:   '',
    dept:         'surgery',
    or_bed_id:    '' as string | number,
    surgeon_id:   '',
    eye:          '',
    procedure:    '',
    anaesthesia:  '',
    pre_op_notes: '',
    scheduled_at: '',
});

function submitSchedule() {
    scheduleForm.post('/surgery', {
        onSuccess: () => {
            showSchedule.value = false;
            scheduleForm.reset();
        },
    });
}

/* ── Report Modal ── */
const showReport   = ref(false);
const reportTarget = ref<string>('');
const reportForm   = useForm({
    op_report:      '',
    post_op_notes:  '',
    complications:  '',
});

function openReport(id: string) {
    reportTarget.value = id;
    reportForm.reset();
    showReport.value = true;
}

function submitReport() {
    reportForm.post(`/surgery/${reportTarget.value}/report`, {
        onSuccess: () => {
            showReport.value = false;
        },
    });
}

/* ── Supplies Modal ── */
const showSupplies   = ref(false);
const suppliesTarget = ref<string>('');

interface SupplyItem { name: string; qty: number; unit_cost: number }
const supplyItems  = ref<SupplyItem[]>([{ name: '', qty: 1, unit_cost: 0 }]);

const suppliesTotal = computed(() =>
    supplyItems.value.reduce((sum, i) => sum + i.qty * i.unit_cost, 0),
);

function addSupplyRow() {
    supplyItems.value.push({ name: '', qty: 1, unit_cost: 0 });
}
function removeSupplyRow(idx: number) {
    supplyItems.value.splice(idx, 1);
}

function openSupplies(id: string) {
    suppliesTarget.value = id;
    supplyItems.value    = [{ name: '', qty: 1, unit_cost: 0 }];
    showSupplies.value   = true;
}

function submitSupplies() {
    router.post(`/surgery/${suppliesTarget.value}/supplies`, {
        surgery_id: suppliesTarget.value,
        items:      supplyItems.value,
    }, {
        onSuccess: () => {
 showSupplies.value = false; 
},
    });
}

const eyeLabel: Record<string, string> = { OD: 'عين يمنى', OS: 'عين يسرى', OU: 'كلاهما' };
</script>

<template>
    <Head title="قسم العمليات" />

    <!-- Header -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">قسم العمليات الجراحية</h2>
        <div class="flex items-center gap-2">
            <select
                v-model="statusFilter"
                class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm text-hospital-text focus:border-hospital-primary focus:outline-none"
                @change="applyFilters"
            >
                <option value="">جميع الحالات</option>
                <option value="scheduled">مجدولة</option>
                <option value="prep">تحضير</option>
                <option value="in_progress">جارية</option>
                <option value="completed">مكتملة</option>
                <option value="cancelled">ملغاة</option>
            </select>
            <button
                class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90 transition-colors"
                @click="showSchedule = true"
            >
                <CalendarPlus class="h-4 w-4" />
                جدولة عملية
            </button>
        </div>
    </div>

    <!-- Table -->
    <DataTable
        :columns="columns"
        :rows="surgeries.data"
        :current-page="surgeries.current_page"
        :last-page="surgeries.last_page"
        :total="surgeries.total"
        empty-text="لا توجد عمليات مسجلة"
        @page="goToPage"
    >
        <template #cell-scheduled_at="{ value }">
            {{ value ? (value as string).replace('T', ' ').slice(0, 16) : '—' }}
        </template>
        <template #cell-file_no="{ row }">
            {{ (row as Surgery).booking?.file_no ?? '—' }}
        </template>
        <template #cell-patient="{ row }">
            {{ (row as Surgery).booking?.patient_name ?? '—' }}
        </template>
        <template #cell-eye="{ value }">
            {{ value ? eyeLabel[value as string] ?? value : '—' }}
        </template>
        <template #cell-surgeon="{ row }">
            {{ (row as Surgery).surgeon?.name ?? '—' }}
        </template>
        <template #cell-or_bed="{ row }">
            <span v-if="(row as Surgery).or_bed">
                {{ (row as Surgery).or_bed!.room.name }} — {{ (row as Surgery).or_bed!.bed_number }}
            </span>
            <span v-else class="text-hospital-text-2">—</span>
        </template>
        <template #cell-status="{ value }">
            <Badge :variant="value as 'scheduled' | 'prep' | 'in_progress' | 'completed' | 'cancelled'" />
        </template>
        <template #cell-supply_total="{ value }">
            <span class="font-mono text-sm">{{ Number(value).toLocaleString('ar-EG') }} ج.م</span>
        </template>
        <template #actions="{ row }">
            <div class="flex items-center gap-1">
                <button
                    class="flex items-center gap-1 rounded px-2 py-1.5 text-xs font-medium text-hospital-primary hover:bg-hospital-primary-pale transition-colors"
                    @click="openReport((row as Surgery).id)"
                >
                    <ClipboardList class="h-3.5 w-3.5" />
                    تقرير
                </button>
                <button
                    class="flex items-center gap-1 rounded px-2 py-1.5 text-xs font-medium text-hospital-accent hover:bg-hospital-accent/10 transition-colors"
                    @click="openSupplies((row as Surgery).id)"
                >
                    <Package class="h-3.5 w-3.5" />
                    مستلزمات
                </button>
            </div>
        </template>
    </DataTable>

    <!-- Schedule Modal -->
    <Modal v-model="showSchedule" title="جدولة عملية جراحية" size="lg">
        <form class="space-y-4" @submit.prevent="submitSchedule">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-hospital-text">رقم الحجز</label>
                    <input
                        v-model="scheduleForm.booking_id"
                        type="text"
                        placeholder="معرّف الحجز"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    />
                    <p v-if="scheduleForm.errors.booking_id" class="mt-1 text-xs text-hospital-danger">{{ scheduleForm.errors.booking_id }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-hospital-text">السرير</label>
                    <select
                        v-model="scheduleForm.or_bed_id"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    >
                        <option value="">— اختر السرير —</option>
                        <option v-for="bed in availableBeds" :key="bed.id" :value="bed.id">
                            {{ bed.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-hospital-text">العين</label>
                    <select
                        v-model="scheduleForm.eye"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    >
                        <option value="">—</option>
                        <option value="OD">عين يمنى (OD)</option>
                        <option value="OS">عين يسرى (OS)</option>
                        <option value="OU">كلاهما (OU)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-hospital-text">التخدير</label>
                    <select
                        v-model="scheduleForm.anaesthesia"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    >
                        <option value="">—</option>
                        <option value="local">موضعي (Local)</option>
                        <option value="topical">سطحي (Topical)</option>
                        <option value="sedation">مهدئ (Sedation)</option>
                        <option value="general">عام (General)</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium text-hospital-text">الإجراء</label>
                    <input
                        v-model="scheduleForm.procedure"
                        type="text"
                        placeholder="اسم الإجراء الجراحي"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    />
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium text-hospital-text">موعد العملية</label>
                    <input
                        v-model="scheduleForm.scheduled_at"
                        type="datetime-local"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    />
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium text-hospital-text">ملاحظات ما قبل العملية</label>
                    <textarea
                        v-model="scheduleForm.pre_op_notes"
                        rows="3"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showSchedule = false">
                    إلغاء
                </button>
                <button
                    type="submit"
                    :disabled="scheduleForm.processing"
                    class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90 disabled:opacity-60"
                >
                    جدولة
                </button>
            </div>
        </form>
    </Modal>

    <!-- Report Modal -->
    <Modal v-model="showReport" title="تقرير العملية" size="md">
        <form class="space-y-4" @submit.prevent="submitReport">
            <div>
                <label class="mb-1 block text-sm font-medium text-hospital-text">تقرير العملية</label>
                <textarea v-model="reportForm.op_report" rows="4" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-hospital-text">ملاحظات ما بعد العملية</label>
                <textarea v-model="reportForm.post_op_notes" rows="3" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-hospital-text">المضاعفات</label>
                <textarea v-model="reportForm.complications" rows="2" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showReport = false">إلغاء</button>
                <button type="submit" :disabled="reportForm.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90 disabled:opacity-60">
                    حفظ التقرير
                </button>
            </div>
        </form>
    </Modal>

    <!-- Supplies Modal -->
    <Modal v-model="showSupplies" title="تسجيل المستلزمات المستخدمة" size="lg">
        <div class="space-y-3">
            <div
                v-for="(item, idx) in supplyItems"
                :key="idx"
                class="grid grid-cols-12 items-center gap-2"
            >
                <input
                    v-model="item.name"
                    type="text"
                    placeholder="اسم الصنف"
                    class="col-span-5 rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                />
                <input
                    v-model.number="item.qty"
                    type="number"
                    min="1"
                    placeholder="الكمية"
                    class="col-span-3 rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                />
                <input
                    v-model.number="item.unit_cost"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="السعر"
                    class="col-span-3 rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                />
                <button
                    class="col-span-1 flex h-9 w-9 items-center justify-center rounded-lg text-hospital-danger hover:bg-hospital-danger/10 transition-colors"
                    @click="removeSupplyRow(idx)"
                >
                    ×
                </button>
            </div>

            <button
                class="text-sm text-hospital-primary hover:underline"
                @click="addSupplyRow"
            >
                + إضافة صنف
            </button>

            <div class="border-t border-hospital-border pt-3 text-left font-mono text-sm font-semibold">
                الإجمالي: {{ suppliesTotal.toLocaleString('ar-EG') }} ج.م
            </div>

            <div class="flex justify-end gap-2">
                <button class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showSupplies = false">إلغاء</button>
                <button
                    class="rounded-lg bg-hospital-accent px-4 py-2 text-sm font-medium text-white hover:bg-hospital-accent/90"
                    @click="submitSupplies"
                >
                    تسجيل المستلزمات
                </button>
            </div>
        </div>
    </Modal>
</template>
