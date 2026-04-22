<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { 
    Users, 
    Search, 
    Calendar, 
    CreditCard, 
    Eye, 
    CheckCircle2, 
    AlertCircle, 
    ChevronLeft,
    FileText,
    DollarSign
} from 'lucide-vue-next'
import AppLayout from '@/components/layout/AppLayout.vue'

defineOptions({ layout: AppLayout })

interface Doctor {
    id: string
    name: string
    specialty: string
    fee_type: string
    fee_value: number
    claim: number
}

const props = defineProps<{
    doctors: Doctor[]
    period: { from: string; to: string }
}>()

const search = ref('')
const selectedDoctor = ref<Doctor | null>(null)
const drDetails = ref<any>(null)
const showPayModal = ref(false)
const showDetailsModal = ref(false)

const filteredDoctors = computed(() => {
    return props.doctors.filter(d => 
        d.name.includes(search.value) || d.specialty.includes(search.value)
    )
})

const payForm = useForm({
    amount: 0,
    period_from: props.period.from,
    period_to: props.period.to,
    method: 'cash',
    notes: '',
})

async function viewDetails(doctor: Doctor) {
    selectedDoctor.value = doctor
    drDetails.value = null
    showDetailsModal.value = true
    
    const res = await fetch(`/doctor/claims/${doctor.id}/calculate?from=${props.period.from}&to=${props.period.to}`)
    drDetails.value = await res.json()
}

function openPay(doctor: Doctor) {
    selectedDoctor.value = doctor
    payForm.amount = doctor.claim
    showPayModal.value = true
}

function submitPay() {
    if (!selectedDoctor.value) return
    payForm.post(`/doctor/claims/${selectedDoctor.value.id}/pay`, {
        onSuccess: () => {
            showPayModal.value = false
            selectedDoctor.value = null
        }
    })
}

function updatePeriod(e: Event, type: 'from' | 'to') {
    const val = (e.target as HTMLInputElement).value
    const params = { ...props.period, [type]: val }
    router.get('/doctor/claims', params, { preserveState: true })
}

const fmt = (val: number) => new Intl.NumberFormat('ar-EG').format(val)
</script>

<template>
    <Head title="مستحقات الأطباء" />

    <div class="space-y-6 pb-12">
        <!-- Header & Toolbar -->
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-[15px] font-bold text-hospital-text">مستحقات الأطباء</h2>
                <p class="text-[10px] text-hospital-text-3">احتساب وصرف حصص الأطباء عن العمليات والكشوفات</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Period Search -->
                <div class="flex items-center gap-2 rounded-xl border border-hospital-border bg-white p-1">
                    <input 
                        type="date" 
                        :value="period.from" 
                        @change="updatePeriod($event, 'from')"
                        class="h-8 border-0 bg-transparent text-[11px] font-bold focus:ring-0" 
                    />
                    <ChevronLeft class="h-3 w-3 text-hospital-text-3" />
                    <input 
                        type="date" 
                        :value="period.to" 
                        @change="updatePeriod($event, 'to')"
                        class="h-8 border-0 bg-transparent text-[11px] font-bold focus:ring-0" 
                    />
                </div>

                <div class="relative">
                    <Search class="absolute right-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-hospital-text-3" />
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="ابحث عن طبيب..."
                        class="h-9 w-[200px] border border-hospital-border bg-white pr-9 pl-4 text-[12px] rounded-[8px] focus:border-hospital-primary focus:ring-0"
                    />
                </div>
            </div>
        </div>

        <!-- Doctors Grid -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            <div 
                v-for="dr in filteredDoctors" 
                :key="dr.id"
                class="card group relative overflow-hidden rounded-[var(--rl)] border border-hospital-border bg-white p-5 transition-all hover:border-hospital-primary hover:shadow-lg [box-shadow:var(--sh)]"
            >
                <!-- Decorative Bar -->
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-hospital-primary/10 group-hover:bg-hospital-primary transition-colors"></div>

                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-hospital-primary-pale text-hospital-primary group-hover:bg-hospital-primary group-hover:text-white transition-all">
                            <Users class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-[13px] font-bold text-hospital-text">{{ dr.name }}</h3>
                            <p class="text-[10px] text-hospital-text-3">{{ dr.specialty }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-5 grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-hospital-bg/50 p-3">
                        <p class="text-[9px] font-bold text-hospital-text-3 uppercase mb-1">نظام الحساب</p>
                        <p class="text-[11px] font-bold text-hospital-text">
                            {{ dr.fee_type === 'percentage' ? 'نسبة' : 'مبلغ ثابت' }} 
                            ({{ dr.fee_value }}{{ dr.fee_type === 'percentage' ? '%' : ' ج' }})
                        </p>
                    </div>
                    <div class="rounded-xl bg-hospital-primary-pale p-3">
                        <p class="text-[9px] font-bold text-hospital-primary uppercase mb-1">صافي المستحق</p>
                        <p class="text-[13px] font-bold text-hospital-primary">{{ fmt(dr.claim) }} ج</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button 
                        @click="viewDetails(dr)"
                        class="flex-1 rounded-[7px] border border-hospital-border py-2 text-[11px] font-bold text-hospital-text-2 transition-all hover:bg-hospital-bg"
                    >
                        المراجعة
                    </button>
                    <button 
                        @click="openPay(dr)"
                        :disabled="dr.claim <= 0"
                        class="flex-1 rounded-[7px] bg-hospital-primary py-2 text-[11px] font-bold text-white transition-all hover:bg-hospital-primary-dark disabled:opacity-30"
                    >
                        صرف المبلغ
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-hospital-text/20 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-[var(--rl)] border border-hospital-border bg-white shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="border-b border-hospital-border bg-hospital-surface-2 px-6 py-4">
                    <h3 class="text-[14px] font-bold text-hospital-text">تأكيد صرف المستحقات</h3>
                </div>
                
                <form @submit.prevent="submitPay" class="p-6 space-y-5">
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-hospital-primary-pale border border-hospital-primary/10">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-hospital-primary shadow-sm">
                            <DollarSign class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-hospital-primary uppercase">المبلغ الإجمالي للطبيب</p>
                            <p class="text-[18px] font-bold text-hospital-primary">{{ fmt(payForm.amount) }} ج.م</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="fg">
                            <label class="text-[11px] font-bold text-hospital-text-3 mb-1.5 block">طريقة الصرف</label>
                            <select v-model="payForm.method" class="w-full rounded-[8px] border border-hospital-border bg-white px-3 py-2 text-[12px] font-bold">
                                <option value="cash">نقداً (خزينة)</option>
                                <option value="bank">تحويل بنكي</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="text-[11px] font-bold text-hospital-text-3 mb-1.5 block">المبلغ الفعلي</label>
                            <input v-model.number="payForm.amount" type="number" step="0.01" class="w-full rounded-[8px] border border-hospital-border px-3 py-2 text-[12px] font-bold" />
                        </div>
                    </div>

                    <div class="fg">
                        <label class="text-[11px] font-bold text-hospital-text-3 mb-1.5 block">ملاحظات</label>
                        <textarea v-model="payForm.notes" class="w-full rounded-[8px] border border-hospital-border p-3 text-[12px] min-h-[80px]" placeholder="أضف أي تفاصيل إضافية هنا..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button 
                            type="button" @click="showPayModal = false"
                            class="flex-1 rounded-[8px] border border-hospital-border py-2.5 text-[12px] font-bold text-hospital-text-3 transition-all hover:bg-hospital-bg"
                        >
                            إلغاء
                        </button>
                        <button 
                            type="submit" :disabled="payForm.processing"
                            class="flex-1 rounded-[8px] bg-hospital-primary py-2.5 text-[12px] font-bold text-white transition-all hover:bg-hospital-primary-dark shadow-lg shadow-hospital-primary/20"
                        >
                            {{ payForm.processing ? 'جارِ التسجيل...' : 'تأكيد الصرف الآن' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Details Modal -->
        <div v-if="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-hospital-text/20 backdrop-blur-sm">
            <div class="w-full max-w-2xl rounded-[var(--rl)] border border-hospital-border bg-white shadow-2xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-200">
                <div class="flex items-center justify-between border-b border-hospital-border bg-hospital-surface-2 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <FileText class="h-4 w-4 text-hospital-primary" />
                        <h3 class="text-[14px] font-bold text-hospital-text">كشف تفصيلي: {{ selectedDoctor?.name }}</h3>
                    </div>
                    <button @click="showDetailsModal = false" class="text-hospital-text-3 hover:text-hospital-danger">&times;</button>
                </div>
                
                <div class="p-0 max-h-[60vh] overflow-y-auto">
                    <div v-if="!drDetails" class="p-12 flex flex-col items-center justify-center gap-3">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-hospital-primary/20 border-t-hospital-primary"></div>
                        <p class="text-[11px] text-hospital-text-3">جارِ تحليل البيانات...</p>
                    </div>
                    
                    <table v-else class="w-full text-right">
                        <thead class="sticky top-0 bg-hospital-surface-2 z-10 border-b border-hospital-border">
                            <tr>
                                <th class="px-6 py-3 text-[10px] font-bold text-hospital-text-3">التاريخ</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-hospital-text-3">المريض</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-hospital-text-3 text-left">قيمة الحجز</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-hospital-text-3 text-left">الحصة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hospital-border/50">
                            <tr v-for="row in drDetails.details" :key="row.booking_id" class="hover:bg-hospital-primary-pale transition-colors">
                                <td class="px-6 py-3 text-[11px] font-mono text-hospital-text-2">{{ row.date }}</td>
                                <td class="px-6 py-3 text-[11px] font-bold text-hospital-text">{{ row.patient }}</td>
                                <td class="px-6 py-3 text-[11px] font-mono text-left">{{ fmt(row.revenue) }}</td>
                                <td class="px-6 py-3 text-[11px] font-mono font-bold text-hospital-primary text-left">{{ fmt(row.claim) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-hospital-border bg-hospital-surface-2 px-6 py-4 flex items-center justify-between">
                    <div class="flex gap-6">
                        <div class="flex flex-col">
                            <span class="text-[9px] text-hospital-text-3">إجمالي الكشوفات</span>
                            <span class="text-[11px] font-bold">{{ drDetails?.stats.booking_count }} حجز</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-hospital-text-3">إجمالي الإيراد</span>
                            <span class="text-[11px] font-bold">{{ fmt(drDetails?.stats.total_revenue || 0) }} ج</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 rounded-lg bg-hospital-primary px-4 py-2 text-white">
                        <span class="text-[10px] font-medium opacity-80 uppercase leading-none">إجمالي المستحق</span>
                        <span class="text-[15px] font-bold leading-none">{{ fmt(drDetails?.stats.total_claim || 0) }} ج</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Scoped select reset */
select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23666' %3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 0.75rem center;
    background-size: 1rem;
}
</style>
