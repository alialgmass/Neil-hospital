<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Calculator, CreditCard, X, FileText, ChevronLeft, ChevronRight, UserCircle, Printer, Search, PackageOpen } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/components/shared/Modal.vue';

interface DoctorSummary {
    doctor: { id: string; name: string; fee_type: string };
    total_claims: number;
    paid_amount: number;
    net_due: number;
}

interface SupplyItem {
    name: string;
    qty: number;
    unit_cost: number;
    total?: number;
}

interface ClaimRow {
    booking_id: string;
    file_no: string;
    patient_name: string;
    date: string;
    dept: string;
    service: string;
    paid: number;
    ins_amount: number;
    dr_share: number;
    supplies?: SupplyItem[];
    supply_total?: number;
}

interface PaymentRecord {
    id: string;
    amount: number;
    paid_at: string;
    method: string;
    notes?: string;
}

interface Claims {
    doctor: { id: string; name: string; fee_type: string };
    period_from: string;
    period_to: string;
    total_claims: number;
    paid_amount: number;
    net_due: number;
    rows: ClaimRow[];
    payments: PaymentRecord[];
}

const props = defineProps<{
    summaries: DoctorSummary[];
    claims: Claims | null;
    filters: { doctor_id?: string; from?: string; to?: string };
}>();

const mounted = ref(false);
onMounted(() => { mounted.value = true; });

// ── Date filter ──
const fromFilter = ref(props.filters.from ?? '');
const toFilter = ref(props.filters.to ?? '');

function applyFilter() {
    router.get('/dr-claims', {
        from: fromFilter.value || undefined,
        to: toFilter.value || undefined,
    }, { preserveState: true });
}

// ── Doctor table state ──
const search = ref('');
const currentPage = ref(1);
const perPage = 10;

const filteredSummaries = computed(() =>
    props.summaries.filter(s => s.doctor.name.includes(search.value)),
);

const totalPages = computed(() => Math.max(1, Math.ceil(filteredSummaries.value.length / perPage)));

const paginatedSummaries = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredSummaries.value.slice(start, start + perPage);
});

watch(search, () => { currentPage.value = 1; });

// ── Doctor detail panel ──
function loadDoctor(doctorId: string) {
    router.get('/dr-claims/calculate', {
        doctor_id: doctorId,
        from: fromFilter.value || undefined,
        to: toFilter.value || undefined,
    }, { preserveState: true });
}

function closePanel() {
    router.get('/dr-claims', {
        from: fromFilter.value || undefined,
        to: toFilter.value || undefined,
    }, { preserveState: true });
}

// ── Row invoice modal ──
const selectedRow = ref<ClaimRow | null>(null);

// ── Pay modal ──
const showPay = ref(false);
const payForm = useForm({
    doctor_id: '',
    amount: 0 as number,
    period_from: '',
    period_to: '',
    paid_at: new Date().toISOString().slice(0, 10),
    method: 'cash' as 'cash' | 'transfer',
    notes: '',
});

function openPay(summary?: DoctorSummary) {
    if (props.claims) {
        payForm.doctor_id = props.claims.doctor.id;
        payForm.amount = props.claims.net_due;
        payForm.period_from = props.claims.period_from;
        payForm.period_to = props.claims.period_to;
    } else if (summary) {
        payForm.doctor_id = summary.doctor.id;
        payForm.amount = summary.net_due;
        payForm.period_from = fromFilter.value;
        payForm.period_to = toFilter.value;
    }
    showPay.value = true;
}

function submitPay() {
    payForm.post('/dr-claims/pay', {
        preserveState: true,
        onSuccess: () => {
            showPay.value = false;
            if (props.claims) {
                loadDoctor(props.claims.doctor.id);
            }
        },
    });
}

// ── Totals ──
const grandTotal = computed(() => props.summaries.reduce((s, d) => s + d.total_claims, 0));
const grandPaid  = computed(() => props.summaries.reduce((s, d) => s + d.paid_amount, 0));
const grandDue   = computed(() => props.summaries.reduce((s, d) => s + d.net_due, 0));

const surgicalRows = computed(() =>
    (props.claims?.rows ?? []).filter(r => (r.supplies?.length ?? 0) > 0),
);

const deptLabels: Record<string, string> = {
    clinic: 'عيادة', labs: 'فحوصات', surgery: 'عمليات', lasik: 'ليزك', laser: 'ليزر',
};

const feeTypeLabel: Record<string, string> = {
    percentage: 'نسبة %',
    fixed: 'مبلغ ثابت',
    insurance: 'تأمين',
};

function fmt(n: number) {
    return Number(n ?? 0).toLocaleString('ar-EG', { minimumFractionDigits: 2 }) + ' ج.م';
}

function printInvoice() {
    window.print();
}
</script>

<template>
    <Head title="مستحقات الأطباء" />

    <!-- Page header + date filter -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-hospital-text">مستحقات الأطباء</h2>
            <p class="text-xs text-hospital-text-3">احتساب وصرف حصص الأطباء عن العمليات والكشوفات</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1">
                <label class="text-xs text-hospital-text-2">من</label>
                <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-white px-3 py-1.5 text-sm focus:border-hospital-primary focus:outline-none" />
            </div>
            <div class="flex items-center gap-1">
                <label class="text-xs text-hospital-text-2">إلى</label>
                <input v-model="toFilter" type="date" class="rounded-lg border border-hospital-border bg-white px-3 py-1.5 text-sm focus:border-hospital-primary focus:outline-none" />
            </div>
            <button
                class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-1.5 text-sm font-medium text-white hover:bg-hospital-primary/90"
                @click="applyFilter"
            >
                <Calculator class="h-4 w-4" /> عرض
            </button>
        </div>
    </div>

    <!-- Grand totals strip -->
    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-hospital-border bg-white p-4 shadow-sm">
            <p class="text-xs text-hospital-text-2">إجمالي المستحقات</p>
            <p class="mt-1 font-mono text-xl font-bold text-hospital-primary">{{ fmt(grandTotal) }}</p>
        </div>
        <div class="rounded-xl border border-hospital-border bg-white p-4 shadow-sm">
            <p class="text-xs text-hospital-text-2">إجمالي المدفوع</p>
            <p class="mt-1 font-mono text-xl font-bold text-hospital-success">{{ fmt(grandPaid) }}</p>
        </div>
        <div class="rounded-xl border border-hospital-border bg-white p-4 shadow-sm">
            <p class="text-xs text-hospital-text-2">إجمالي المتبقي</p>
            <p class="mt-1 font-mono text-xl font-bold" :class="grandDue > 0 ? 'text-hospital-danger' : 'text-hospital-success'">{{ fmt(grandDue) }}</p>
        </div>
    </div>

    <!-- Doctors table card -->
    <div class="overflow-hidden rounded-[var(--rl)] border border-hospital-border bg-white [box-shadow:var(--sh)]">
        <!-- Table toolbar -->
        <div class="flex items-center justify-between border-b border-hospital-border bg-hospital-surface-2 px-4 py-3">
            <p class="text-[13px] font-bold text-hospital-text">
                قائمة الأطباء
                <span class="ml-1 text-[11px] font-normal text-hospital-text-3">({{ filteredSummaries.length }} طبيب)</span>
            </p>
            <div class="relative">
                <Search class="absolute right-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-hospital-text-3" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="ابحث باسم الطبيب..."
                    class="h-8 w-[200px] rounded-[7px] border border-hospital-border bg-white pr-9 pl-3 text-[12px] focus:border-hospital-primary focus:outline-none"
                />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="border-b border-hospital-border bg-hospital-bg/50">
                        <th class="px-4 py-3 text-[10px] font-bold text-hospital-text-3 w-8">#</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-hospital-text-3">الطبيب</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-hospital-text-3">نوع الحساب</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-hospital-text-3 text-left">إجمالي المستحق</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-hospital-text-3 text-left">المدفوع</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-hospital-text-3 text-left">المتبقي</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-hospital-text-3 text-center w-28">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hospital-border/60">
                    <tr
                        v-for="(s, index) in paginatedSummaries"
                        :key="s.doctor.id"
                        class="cursor-pointer transition-colors hover:bg-hospital-primary-pale/30"
                        :class="{ 'bg-hospital-primary-pale/40': claims?.doctor.id === s.doctor.id }"
                        @click="loadDoctor(s.doctor.id)"
                    >
                        <td class="px-4 py-3 text-[11px] text-hospital-text-3">
                            {{ (currentPage - 1) * perPage + index + 1 }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-hospital-primary-pale text-hospital-primary">
                                    <UserCircle class="h-4 w-4" />
                                </div>
                                <span class="text-[12px] font-bold text-hospital-text">{{ s.doctor.name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-md bg-hospital-bg px-2 py-1 text-[10px] font-bold text-hospital-text-2">
                                {{ feeTypeLabel[s.doctor.fee_type] ?? s.doctor.fee_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-left font-mono text-[12px] font-bold text-hospital-primary">
                            {{ fmt(s.total_claims) }}
                        </td>
                        <td class="px-4 py-3 text-left font-mono text-[12px] font-bold text-hospital-success">
                            {{ fmt(s.paid_amount) }}
                        </td>
                        <td class="px-4 py-3 text-left font-mono text-[12px] font-bold" :class="s.net_due > 0 ? 'text-hospital-danger' : 'text-hospital-text-3'">
                            {{ fmt(s.net_due) }}
                        </td>
                        <td class="px-4 py-3" @click.stop>
                            <div class="flex items-center justify-center gap-1.5">
                                <button
                                    class="rounded-[6px] border border-hospital-border px-2.5 py-1 text-[10px] font-bold text-hospital-text-2 transition-all hover:bg-hospital-bg"
                                    @click="loadDoctor(s.doctor.id)"
                                >
                                    تفاصيل
                                </button>
                                <button
                                    v-if="s.net_due > 0"
                                    class="rounded-[6px] bg-hospital-success px-2.5 py-1 text-[10px] font-bold text-white transition-all hover:bg-hospital-success/90"
                                    @click="openPay(s)"
                                >
                                    صرف
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="paginatedSummaries.length === 0">
                        <td colspan="7" class="py-12 text-center text-[12px] text-hospital-text-3">
                            {{ summaries.length === 0 ? 'لا يوجد أطباء نشطون' : 'لا توجد نتائج مطابقة للبحث' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-between border-t border-hospital-border px-5 py-3">
            <p class="text-[11px] text-hospital-text-3">صفحة {{ currentPage }} من {{ totalPages }}</p>
            <div class="flex items-center gap-1">
                <button
                    :disabled="currentPage === 1"
                    class="flex h-7 w-7 items-center justify-center rounded-[6px] border border-hospital-border text-hospital-text-2 hover:bg-hospital-bg disabled:opacity-40"
                    @click="currentPage--"
                >
                    <ChevronRight class="h-3.5 w-3.5" />
                </button>
                <template v-for="p in totalPages" :key="p">
                    <button
                        v-if="Math.abs(p - currentPage) <= 2 || p === 1 || p === totalPages"
                        class="flex h-7 w-7 items-center justify-center rounded-[6px] text-[11px] font-bold transition-all"
                        :class="p === currentPage ? 'bg-hospital-primary text-white' : 'border border-hospital-border text-hospital-text-2 hover:bg-hospital-bg'"
                        @click="currentPage = p"
                    >{{ p }}</button>
                    <span v-else-if="Math.abs(p - currentPage) === 3" class="px-1 text-[11px] text-hospital-text-3">…</span>
                </template>
                <button
                    :disabled="currentPage === totalPages"
                    class="flex h-7 w-7 items-center justify-center rounded-[6px] border border-hospital-border text-hospital-text-2 hover:bg-hospital-bg disabled:opacity-40"
                    @click="currentPage++"
                >
                    <ChevronLeft class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    </div>

    <!-- ════════════════ Right slide-over: doctor detail ════════════════ -->
    <Teleport v-if="mounted" to="body">
        <Transition name="claims-fade">
            <div v-if="claims" class="fixed inset-0 z-30 bg-black/30" @click="closePanel" />
        </Transition>

        <Transition name="claims-slide">
            <div v-if="claims" class="fixed inset-y-0 left-0 z-40 flex w-full max-w-2xl flex-col bg-white shadow-2xl" dir="rtl">
                <!-- Panel header -->
                <div class="flex shrink-0 items-center justify-between bg-linear-to-l from-blue-700 to-blue-900 px-5 py-4 text-white">
                    <div>
                        <p class="text-base font-bold">{{ claims.doctor.name }}</p>
                        <p class="text-xs opacity-75">{{ claims.period_from }} — {{ claims.period_to }}</p>
                    </div>
                    <button class="rounded-full p-1 hover:bg-white/20" @click="closePanel">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Summary cards -->
                <div class="grid shrink-0 grid-cols-3 gap-3 border-b border-hospital-border bg-hospital-bg p-4">
                    <div class="rounded-lg bg-white p-3 text-center shadow-sm">
                        <p class="text-[10px] text-hospital-text-2">إجمالي المستحقات</p>
                        <p class="font-mono text-sm font-bold text-hospital-primary">{{ fmt(claims.total_claims) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-3 text-center shadow-sm">
                        <p class="text-[10px] text-hospital-text-2">المدفوع للطبيب</p>
                        <p class="font-mono text-sm font-bold text-hospital-success">{{ fmt(claims.paid_amount) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-3 text-center shadow-sm">
                        <p class="text-[10px] text-hospital-text-2">المتبقي</p>
                        <p class="font-mono text-sm font-bold" :class="claims.net_due > 0 ? 'text-hospital-danger' : 'text-hospital-success'">{{ fmt(claims.net_due) }}</p>
                    </div>
                </div>

                <!-- Payments made to doctor -->
                <div v-if="claims.payments.length" class="shrink-0 border-b border-hospital-border bg-white px-4 py-3">
                    <p class="mb-2 text-xs font-bold text-hospital-text-2">الدفعات المسددة للطبيب</p>
                    <div class="space-y-1.5">
                        <div
                            v-for="p in claims.payments"
                            :key="p.id"
                            class="flex items-center justify-between rounded-lg bg-hospital-bg px-3 py-2 text-xs"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-hospital-text-2">{{ p.paid_at }}</span>
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-medium" :class="p.method === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'">
                                    {{ p.method === 'cash' ? 'نقدي' : 'تحويل' }}
                                </span>
                                <span v-if="p.notes" class="text-hospital-text-3">{{ p.notes }}</span>
                            </div>
                            <span class="font-mono font-semibold text-hospital-success">{{ fmt(p.amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Rows table (scrollable) -->
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 border-b border-hospital-border bg-hospital-bg">
                            <tr>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold">التاريخ</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold">المريض</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold">القسم</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold">مدفوع</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold">مستلزمات</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold">مستحق</th>
                                <th class="px-4 py-2.5 w-6" />
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="row in claims.rows" :key="row.booking_id">
                                <tr
                                    class="cursor-pointer border-b border-hospital-border/40 hover:bg-blue-50/50"
                                    @click="selectedRow = row"
                                >
                                    <td class="px-4 py-2.5 text-xs">{{ row.date }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="block text-xs font-medium">{{ row.patient_name }}</span>
                                        <span class="block font-mono text-[10px] text-hospital-text-3">{{ row.file_no }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs">{{ deptLabels[row.dept] ?? row.dept }}</td>
                                    <td class="px-4 py-2.5 text-left font-mono text-xs">{{ fmt(row.paid) }}</td>
                                    <td class="px-4 py-2.5 text-left font-mono text-xs" :class="(row.supply_total ?? 0) > 0 ? 'font-semibold text-hospital-warning' : 'text-hospital-text-3'">
                                        {{ (row.supply_total ?? 0) > 0 ? fmt(row.supply_total!) : '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-left font-mono text-xs font-semibold text-hospital-primary">{{ fmt(row.dr_share) }}</td>
                                    <td class="px-4 py-2.5">
                                        <FileText class="h-3.5 w-3.5 text-hospital-text-3" />
                                    </td>
                                </tr>
                                <!-- Supply items sub-row -->
                                <tr v-if="row.supplies && row.supplies.length > 0" class="border-b border-hospital-border/40 bg-amber-50/40">
                                    <td colspan="7" class="px-6 pb-2.5 pt-1">
                                        <div class="flex items-start gap-2">
                                            <PackageOpen class="mt-0.5 h-3.5 w-3.5 shrink-0 text-hospital-warning" />
                                            <div>
                                                <p class="mb-1 text-[9px] font-bold uppercase text-hospital-warning">مستلزمات جراحية</p>
                                                <div class="flex flex-wrap gap-2">
                                                    <span
                                                        v-for="(item, i) in row.supplies"
                                                        :key="i"
                                                        class="rounded bg-white px-2 py-0.5 text-[10px] text-hospital-text-2 shadow-sm"
                                                    >
                                                        {{ item.name }} × {{ item.qty }} = <strong>{{ fmt(item.total ?? item.qty * item.unit_cost) }}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="claims.rows.length === 0">
                                <td colspan="7" class="p-10 text-center text-sm text-hospital-text-2">
                                    لا توجد حالات مسددة في هذه الفترة
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer action -->
                <div class="flex shrink-0 items-center justify-between border-t border-hospital-border p-4">
                    <button
                        class="flex items-center gap-1.5 rounded-lg border border-hospital-border px-3 py-2 text-sm text-hospital-text-2 hover:bg-hospital-bg"
                        @click="printInvoice"
                    >
                        <Printer class="h-4 w-4" />
                        طباعة
                    </button>
                    <div class="flex gap-2">
                        <button class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="closePanel">إغلاق</button>
                        <button
                            v-if="claims.net_due > 0"
                            class="flex items-center gap-1.5 rounded-lg bg-hospital-success px-4 py-2 text-sm font-medium text-white hover:bg-hospital-success/90"
                            @click="openPay()"
                        >
                            <CreditCard class="h-4 w-4" />
                            تسجيل دفعة ({{ fmt(claims.net_due) }})
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- ════════════════ Row invoice modal ════════════════ -->
    <Teleport v-if="mounted" to="body">
        <Transition name="claims-fade">
            <div
                v-if="selectedRow && claims"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="selectedRow = null"
            >
                <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" dir="rtl">
                    <div class="flex items-center justify-between rounded-t-2xl bg-linear-to-l from-blue-700 to-blue-900 px-5 py-4 text-white">
                        <div>
                            <p class="text-xs opacity-75">{{ claims.doctor.name }}</p>
                            <p class="text-base font-bold">إيصال حالة — {{ selectedRow.file_no }}</p>
                            <p class="text-xs opacity-75">{{ selectedRow.date }}</p>
                        </div>
                        <button class="rounded-full p-1 hover:bg-white/20" @click="selectedRow = null">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-3 p-5">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-hospital-bg p-3">
                                <p class="text-xs text-hospital-text-2">المريض</p>
                                <p class="font-semibold">{{ selectedRow.patient_name }}</p>
                            </div>
                            <div class="rounded-lg bg-hospital-bg p-3">
                                <p class="text-xs text-hospital-text-2">القسم</p>
                                <p class="font-semibold">{{ deptLabels[selectedRow.dept] ?? selectedRow.dept }}</p>
                            </div>
                            <div class="col-span-2 rounded-lg bg-hospital-bg p-3">
                                <p class="text-xs text-hospital-text-2">الخدمة</p>
                                <p class="font-semibold">{{ selectedRow.service }}</p>
                            </div>
                        </div>

                        <!-- Surgery supplies in row modal -->
                        <div v-if="selectedRow.supplies && selectedRow.supplies.length > 0" class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-bold text-amber-700">
                                <PackageOpen class="h-3.5 w-3.5" /> مستلزمات جراحية
                            </p>
                            <div class="space-y-1">
                                <div v-for="(item, i) in selectedRow.supplies" :key="i" class="flex justify-between text-xs">
                                    <span class="text-amber-800">{{ item.name }} (× {{ item.qty }})</span>
                                    <span class="font-mono font-semibold text-amber-900">{{ fmt(item.total ?? item.qty * item.unit_cost) }}</span>
                                </div>
                                <div class="mt-1 flex justify-between border-t border-amber-200 pt-1 text-xs font-bold text-amber-800">
                                    <span>إجمالي المستلزمات</span>
                                    <span class="font-mono">{{ fmt(selectedRow.supply_total ?? 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-hospital-border p-4">
                            <div class="flex items-center justify-between border-b border-dashed border-hospital-border pb-2 text-sm">
                                <span class="text-hospital-text-2">المبلغ المدفوع من المريض</span>
                                <span class="font-mono">{{ fmt(selectedRow.paid) }}</span>
                            </div>
                            <div v-if="(selectedRow.supply_total ?? 0) > 0" class="flex items-center justify-between border-b border-dashed border-hospital-border py-2 text-sm">
                                <span class="text-hospital-warning">خصم المستلزمات</span>
                                <span class="font-mono text-hospital-warning">− {{ fmt(selectedRow.supply_total!) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 text-base font-bold text-hospital-primary">
                                <span>مستحق الطبيب (هذه الحالة)</span>
                                <span class="font-mono">{{ fmt(selectedRow.dr_share) }}</span>
                            </div>
                        </div>

                        <div class="rounded-xl bg-blue-50 p-3">
                            <p class="mb-2 text-xs font-bold text-blue-700">ملخص الفترة</p>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <p class="text-[10px] text-hospital-text-2">إجمالي مستحق</p>
                                    <p class="font-mono text-xs font-bold text-blue-700">{{ fmt(claims.total_claims) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-hospital-text-2">مدفوع للطبيب</p>
                                    <p class="font-mono text-xs font-bold text-hospital-success">{{ fmt(claims.paid_amount) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-hospital-text-2">المتبقي</p>
                                    <p class="font-mono text-xs font-bold" :class="claims.net_due > 0 ? 'text-hospital-danger' : 'text-hospital-success'">{{ fmt(claims.net_due) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-hospital-border p-4">
                        <button class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="selectedRow = null">إغلاق</button>
                        <button
                            v-if="claims.net_due > 0"
                            class="flex items-center gap-1.5 rounded-lg bg-hospital-success px-4 py-2 text-sm font-medium text-white"
                            @click="selectedRow = null; openPay();"
                        >
                            <CreditCard class="h-4 w-4" /> تسجيل دفعة
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- ════════════════ Pay modal ════════════════ -->
    <Modal v-model="showPay" title="تسجيل دفعة للطبيب" size="md">
        <form class="space-y-4" @submit.prevent="submitPay">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">المبلغ (ج.م)</label>
                    <input v-model.number="payForm.amount" type="number" min="0.01" step="0.01" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="payForm.errors.amount" class="mt-1 text-xs text-hospital-danger">{{ payForm.errors.amount }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">تاريخ الدفع</label>
                    <input v-model="payForm.paid_at" type="date" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                    <p v-if="payForm.errors.paid_at" class="mt-1 text-xs text-hospital-danger">{{ payForm.errors.paid_at }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">طريقة الدفع</label>
                    <select v-model="payForm.method" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="cash">نقدي</option>
                        <option value="transfer">تحويل بنكي</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-sm font-medium">ملاحظات</label>
                    <textarea v-model="payForm.notes" rows="2" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showPay = false">إلغاء</button>
                <button type="submit" :disabled="payForm.processing" class="rounded-lg bg-hospital-success px-4 py-2 text-sm font-medium text-white disabled:opacity-60">تسجيل الدفعة</button>
            </div>
        </form>
    </Modal>

    <!-- ════════════════ Print invoice ════════════════ -->
    <Teleport v-if="mounted && claims" to="body">
        <div id="dr-claims-print" dir="rtl">
            <div class="print-header">
                <div>
                    <div class="print-title">كشف مستحقات الطبيب</div>
                    <div class="print-subtitle">{{ claims.doctor.name }}</div>
                </div>
                <div class="print-period">
                    <div>الفترة</div>
                    <div>{{ claims.period_from }} — {{ claims.period_to }}</div>
                </div>
            </div>

            <div class="print-summary">
                <div class="print-summary-cell">
                    <div class="print-summary-label">إجمالي المستحقات</div>
                    <div class="print-summary-value primary">{{ fmt(claims.total_claims) }}</div>
                </div>
                <div class="print-summary-cell">
                    <div class="print-summary-label">إجمالي المدفوع</div>
                    <div class="print-summary-value success">{{ fmt(claims.paid_amount) }}</div>
                </div>
                <div class="print-summary-cell">
                    <div class="print-summary-label">الصافي المتبقي</div>
                    <div class="print-summary-value" :class="claims.net_due > 0 ? 'danger' : 'success'">{{ fmt(claims.net_due) }}</div>
                </div>
            </div>

            <template v-if="claims.payments.length">
                <div class="print-section-title">الدفعات المسددة للطبيب</div>
                <table class="print-table">
                    <thead>
                        <tr>
                            <th>تاريخ الدفع</th>
                            <th>الطريقة</th>
                            <th>ملاحظات</th>
                            <th class="ltr">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in claims.payments" :key="p.id">
                            <td>{{ p.paid_at }}</td>
                            <td>{{ p.method === 'cash' ? 'نقدي' : 'تحويل بنكي' }}</td>
                            <td>{{ p.notes ?? '—' }}</td>
                            <td class="ltr amount">{{ fmt(p.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </template>

            <div class="print-section-title">تفاصيل الحالات المسددة</div>
            <table class="print-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>رقم الملف</th>
                        <th>المريض</th>
                        <th>القسم</th>
                        <th>الخدمة</th>
                        <th class="ltr">المدفوع</th>
                        <th class="ltr">مستلزمات</th>
                        <th class="ltr">مستحق الطبيب</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="row in claims.rows" :key="row.booking_id">
                        <tr>
                            <td>{{ row.date }}</td>
                            <td class="mono">{{ row.file_no }}</td>
                            <td>{{ row.patient_name }}</td>
                            <td>{{ deptLabels[row.dept] ?? row.dept }}</td>
                            <td>{{ row.service }}</td>
                            <td class="ltr amount">{{ fmt(row.paid) }}</td>
                            <td class="ltr amount" :style="(row.supply_total ?? 0) > 0 ? 'color:#b45309;font-weight:700' : 'color:#9ca3af'">
                                {{ (row.supply_total ?? 0) > 0 ? fmt(row.supply_total!) : '—' }}
                            </td>
                            <td class="ltr amount bold">{{ fmt(row.dr_share) }}</td>
                        </tr>
                        <!-- Supply items sub-rows in print -->
                        <template v-if="row.supplies && row.supplies.length > 0">
                            <tr v-for="(item, i) in row.supplies" :key="`${row.booking_id}-supply-${i}`" class="supply-row">
                                <td colspan="4" class="supply-indent">↳ {{ item.name }}</td>
                                <td>الكمية: {{ item.qty }}</td>
                                <td class="ltr amount" style="color:#9ca3af">{{ fmt(item.unit_cost) }} / وحدة</td>
                                <td class="ltr amount" style="color:#b45309">{{ fmt(item.total ?? item.qty * item.unit_cost) }}</td>
                                <td />
                            </tr>
                        </template>
                    </template>
                </tbody>
                <tfoot>
                    <tr v-if="surgicalRows.length > 0" class="tfoot-sub">
                        <td colspan="7">إجمالي تكاليف المستلزمات الجراحية</td>
                        <td class="ltr amount" style="color:#b45309">{{ fmt(surgicalRows.reduce((s, r) => s + (r.supply_total ?? 0), 0)) }}</td>
                    </tr>
                    <tr class="tfoot-sub">
                        <td colspan="7">إجمالي المستحقات</td>
                        <td class="ltr amount">{{ fmt(claims.total_claims) }}</td>
                    </tr>
                    <tr class="tfoot-sub">
                        <td colspan="7">إجمالي المدفوع للطبيب</td>
                        <td class="ltr amount deduct">− {{ fmt(claims.paid_amount) }}</td>
                    </tr>
                    <tr class="tfoot-net">
                        <td colspan="7" class="bold">الصافي المتبقي</td>
                        <td class="ltr amount bold">{{ fmt(claims.net_due) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="print-footer">
                طُبع بتاريخ {{ new Date().toLocaleDateString('ar-EG') }}
            </div>
        </div>
    </Teleport>
</template>

<style>
.claims-fade-enter-active,
.claims-fade-leave-active { transition: opacity 0.2s ease; }
.claims-fade-enter-from,
.claims-fade-leave-to { opacity: 0; }

.claims-slide-enter-active,
.claims-slide-leave-active { transition: transform 0.25s ease; }
.claims-slide-enter-from,
.claims-slide-leave-to { transform: translateX(-100%); }

/* ── Print ── */
#dr-claims-print { display: none; }

@media print {
    body > *:not(#dr-claims-print) { display: none !important; }
    #dr-claims-print {
        display: block;
        font-family: 'Cairo', 'Segoe UI', sans-serif;
        font-size: 12px;
        color: #111;
        direction: rtl;
        padding: 24px;
    }

    .print-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #1e3a5f;
        padding-bottom: 12px;
        margin-bottom: 16px;
    }
    .print-title  { font-size: 18px; font-weight: 700; color: #1e3a5f; }
    .print-subtitle { font-size: 14px; font-weight: 600; margin-top: 4px; }
    .print-period { text-align: left; font-size: 11px; color: #555; }
    .print-period div:last-child { font-weight: 700; font-size: 13px; color: #111; margin-top: 2px; }

    .print-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    .print-summary-cell { border: 1px solid #ddd; border-radius: 6px; padding: 10px 14px; text-align: center; }
    .print-summary-label { font-size: 10px; color: #666; margin-bottom: 4px; }
    .print-summary-value { font-size: 15px; font-weight: 800; font-family: monospace; }
    .print-summary-value.primary { color: #1e3a5f; }
    .print-summary-value.success { color: #16a34a; }
    .print-summary-value.danger  { color: #dc2626; }

    .print-section-title {
        background: #1e3a5f;
        color: #fff;
        padding: 5px 12px;
        font-size: 11px;
        font-weight: 700;
        margin: 16px 0 6px;
        border-radius: 4px;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-bottom: 8px;
    }
    .print-table th {
        background: #e8f0fe;
        color: #1e3a5f;
        padding: 7px 10px;
        text-align: right;
        font-weight: 700;
        border: 1px solid #c8d8f0;
    }
    .print-table td {
        padding: 6px 10px;
        border: 1px solid #e5e7eb;
        vertical-align: top;
    }
    .print-table tbody tr:nth-child(even) td { background: #f9fafb; }
    .print-table .supply-row td { background: #fffbeb; font-size: 10px; color: #92400e; }
    .print-table .supply-indent { padding-right: 20px; color: #b45309; font-style: italic; }
    .print-table tfoot .tfoot-sub td { background: #f1f5f9; color: #374151; font-size: 11px; border-color: #e2e8f0; }
    .print-table tfoot .tfoot-sub .deduct { color: #16a34a; font-weight: 700; }
    .print-table tfoot .tfoot-net td { background: #1e3a5f; color: #fff; font-weight: 700; font-size: 13px; border-color: #1e3a5f; }
    .print-table .ltr { text-align: left; direction: ltr; }
    .print-table .mono { font-family: monospace; font-size: 10px; }
    .print-table .amount { font-family: monospace; }
    .print-table .bold { font-weight: 700; }

    .print-footer {
        margin-top: 24px;
        text-align: center;
        font-size: 10px;
        color: #888;
        border-top: 1px solid #eee;
        padding-top: 10px;
    }
}
</style>
