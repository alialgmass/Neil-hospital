<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ChevronDown, ChevronUp, FileText, Plus, Printer, Tag, Trash2 } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import Badge from '@/components/shared/Badge.vue'
import Modal from '@/components/shared/Modal.vue'

defineOptions({ layout: AppLayout })

interface Company {
    id: string
    name: string
    coverage_pct: number
}

interface ServiceItem {
    id: string
    name: string
    dept: string
    price: number
    ins_price: number
}

interface PriceListItem {
    service_id: string
    price: number
    service?: { id: string; name: string; dept: string }
}

interface PriceList {
    id: string
    name: string
    type: string
    ins_coverage?: number
    discount_pct: number
    is_active: boolean
    company?: Company
    items: PriceListItem[]
}

const props = defineProps<{
    priceLists: { data: PriceList[]; links: unknown[] }
    companies: Company[]
    services: ServiceItem[]
}>()

const showModal = ref(false)
const expandedList = ref<string | null>(null)

const form = useForm({
    name: '',
    type: 'insurance' as 'cash' | 'insurance' | 'vip' | 'special',
    ins_company_id: '',
    ins_coverage: 80,
    discount_pct: 0,
    notes: '',
    items: [] as { service_id: string; price: number }[],
})

const deptLabels: Record<string, string> = {
    clinic: 'العيادة',
    labs: 'الفحوصات',
    surgery: 'العمليات',
    lasik: 'الليزك',
    laser: 'الليزر',
}

const typeConfig: Record<string, { label: string; variant: string; color: string }> = {
    cash:      { label: 'نقدي',   variant: 'active',  color: 'text-hospital-success' },
    insurance: { label: 'تأمين',  variant: 'info',    color: 'text-hospital-primary' },
    vip:       { label: 'VIP',    variant: 'warning', color: 'text-hospital-warning' },
    special:   { label: 'خاص',   variant: 'partial', color: 'text-hospital-accent' },
}

const totalActive = computed(() => props.priceLists.data.filter((p) => p.is_active).length)
const totalServices = computed(() => props.priceLists.data.reduce((s, p) => s + p.items.length, 0))

function addServiceRow() {
    form.items.push({ service_id: '', price: 0 })
}

function removeServiceRow(index: number) {
    form.items.splice(index, 1)
}

function onServiceSelect(index: number) {
    const svc = props.services.find((s) => s.id === form.items[index].service_id)
    if (svc) {
        form.items[index].price = svc.ins_price || svc.price
    }
}

function openCreate() {
    form.reset()
    form.clearErrors()
    showModal.value = true
}

function submit() {
    form.post('/insurance/price-lists', {
        onSuccess: () => {
            showModal.value = false
            form.reset()
        },
    })
}

function toggleExpand(id: string) {
    expandedList.value = expandedList.value === id ? null : id
}
</script>

<template>
    <div class="space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-hospital-text">قوائم الأسعار</h1>
                <p class="mt-0.5 text-sm text-hospital-text-3">إدارة قوائم أسعار شركات التأمين والزيارات</p>
            </div>
            <button
                class="flex items-center gap-2 rounded-xl bg-hospital-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-hospital-primary/90 active:scale-95 transition-all"
                @click="openCreate"
            >
                <Plus class="h-4 w-4" />
                قائمة جديدة
            </button>
        </div>

        <!-- Stats strip -->
        <div class="grid grid-cols-3 gap-4">
            <div class="flex items-center gap-3 rounded-xl border border-hospital-border bg-hospital-surface px-4 py-3 shadow-sm">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-hospital-primary-pale">
                    <FileText class="h-4 w-4 text-hospital-primary" />
                </div>
                <div>
                    <p class="text-xs text-hospital-text-3">إجمالي القوائم</p>
                    <p class="text-lg font-bold text-hospital-text">{{ priceLists.data.length }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-hospital-border bg-hospital-surface px-4 py-3 shadow-sm">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-hospital-success-pale">
                    <Tag class="h-4 w-4 text-hospital-success" />
                </div>
                <div>
                    <p class="text-xs text-hospital-text-3">قوائم نشطة</p>
                    <p class="text-lg font-bold text-hospital-text">{{ totalActive }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-hospital-border bg-hospital-surface px-4 py-3 shadow-sm">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-hospital-accent-pale">
                    <Tag class="h-4 w-4 text-hospital-accent" />
                </div>
                <div>
                    <p class="text-xs text-hospital-text-3">إجمالي الخدمات</p>
                    <p class="text-lg font-bold text-hospital-text">{{ totalServices }}</p>
                </div>
            </div>
        </div>

        <!-- Price Lists -->
        <div class="space-y-3">
            <div
                v-for="list in priceLists.data"
                :key="list.id"
                class="overflow-hidden rounded-2xl border border-hospital-border bg-hospital-surface shadow-sm transition-shadow hover:shadow-md"
            >
                <!-- Card Header -->
                <div
                    class="flex cursor-pointer items-center justify-between px-5 py-4"
                    @click="toggleExpand(list.id)"
                >
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-hospital-primary-pale">
                            <FileText class="h-5 w-5 text-hospital-primary" />
                        </div>
                        <div>
                            <p class="font-semibold text-hospital-text">{{ list.name }}</p>
                            <p v-if="list.company" class="text-xs text-hospital-text-3">{{ list.company.name }}</p>
                            <p v-else class="text-xs text-hospital-text-3">— بدون شركة تأمين</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Badge :variant="(typeConfig[list.type]?.variant ?? 'info') as any">
                            {{ typeConfig[list.type]?.label ?? list.type }}
                        </Badge>
                        <span
                            v-if="list.ins_coverage"
                            class="rounded-lg bg-hospital-primary-pale px-2.5 py-0.5 text-xs font-bold text-hospital-primary"
                        >
                            تغطية {{ list.ins_coverage }}%
                        </span>
                        <span class="rounded-lg bg-gray-100 px-2.5 py-0.5 text-xs text-hospital-text-3">
                            {{ list.items.length }} خدمة
                        </span>
                        <button
                            class="rounded-lg p-1.5 text-hospital-text-3 hover:bg-hospital-primary-pale hover:text-hospital-primary transition-colors"
                            title="طباعة"
                            @click.stop="() => window.print()"
                        >
                            <Printer class="h-4 w-4" />
                        </button>
                        <div class="text-hospital-text-3">
                            <ChevronUp v-if="expandedList === list.id" class="h-4 w-4" />
                            <ChevronDown v-else class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <!-- Items Table (expanded) -->
                <div v-if="expandedList === list.id" class="border-t border-hospital-border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-hospital-text-3">الخدمة</th>
                                <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-hospital-text-3">القسم</th>
                                <th class="px-5 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-hospital-text-3">السعر</th>
                                <th v-if="list.ins_coverage" class="px-5 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-hospital-primary">يتحمله التأمين</th>
                                <th v-if="list.ins_coverage" class="px-5 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-hospital-warning">يتحمله المريض</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hospital-border">
                            <tr v-for="item in list.items" :key="item.service_id" class="hover:bg-hospital-primary/5 transition-colors">
                                <td class="px-5 py-3 font-medium text-hospital-text">{{ item.service?.name || item.service_id }}</td>
                                <td class="px-5 py-3 text-hospital-text-3">{{ deptLabels[item.service?.dept ?? ''] || '—' }}</td>
                                <td class="px-5 py-3 text-center font-semibold tabular-nums text-hospital-text">
                                    {{ item.price.toFixed(2) }} <span class="text-xs text-hospital-text-3">ج</span>
                                </td>
                                <td v-if="list.ins_coverage" class="px-5 py-3 text-center font-semibold tabular-nums text-hospital-primary">
                                    {{ ((item.price * (list.ins_coverage ?? 0)) / 100).toFixed(2) }} <span class="text-xs text-hospital-text-3">ج</span>
                                </td>
                                <td v-if="list.ins_coverage" class="px-5 py-3 text-center font-semibold tabular-nums text-hospital-warning">
                                    {{ (item.price - (item.price * (list.ins_coverage ?? 0)) / 100).toFixed(2) }} <span class="text-xs text-hospital-text-3">ج</span>
                                </td>
                            </tr>
                            <tr v-if="list.items.length === 0">
                                <td class="px-5 py-8 text-center text-hospital-text-3" colspan="5">لا توجد خدمات في هذه القائمة</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="priceLists.data.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-hospital-border bg-hospital-surface py-16">
                <FileText class="mb-4 h-12 w-12 text-gray-300" />
                <p class="font-semibold text-gray-400">لا توجد قوائم أسعار</p>
                <p class="mt-1 text-sm text-gray-300">ابدأ بإنشاء أول قائمة أسعار</p>
                <button class="mt-4 flex items-center gap-2 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90" @click="openCreate">
                    <Plus class="h-4 w-4" />
                    قائمة جديدة
                </button>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal v-model="showModal" title="إنشاء قائمة أسعار" size="lg" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="submit">
                <!-- Basic Info -->
                <div class="form-section">
                    <p class="form-section-title">
                        <FileText class="h-3.5 w-3.5" />
                        بيانات القائمة
                    </p>
                    <div class="fg">
                        <label>اسم القائمة <span class="text-hospital-danger">*</span></label>
                        <input
                            v-model="form.name"
                            :class="['fi', form.errors.name && 'fi-err']"
                            type="text"
                            placeholder="مثال: قائمة شركة الراجحي 2024"
                            required
                        />
                        <p v-if="form.errors.name" class="form-err-msg">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="fg">
                            <label>نوع القائمة</label>
                            <select v-model="form.type" class="fi">
                                <option value="cash">نقدي</option>
                                <option value="insurance">تأمين</option>
                                <option value="vip">VIP</option>
                                <option value="special">خاص</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label>نسبة الخصم %</label>
                            <div class="relative">
                                <input v-model.number="form.discount_pct" class="fi pl-7" type="number" min="0" max="100" step="0.01" />
                                <span class="pct-badge text-hospital-text-3">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insurance fields -->
                <div v-if="form.type === 'insurance'" class="form-section">
                    <p class="form-section-title">إعدادات التأمين</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="fg col-span-2">
                            <label>شركة التأمين</label>
                            <select v-model="form.ins_company_id" class="fi">
                                <option value="">— اختر شركة —</option>
                                <option v-for="co in companies" :key="co.id" :value="co.id">{{ co.name }}</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label>نسبة التغطية %</label>
                            <div class="relative">
                                <input v-model.number="form.ins_coverage" class="fi pl-7" type="number" min="0" max="100" step="0.01" />
                                <span class="pct-badge text-hospital-primary">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Items -->
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-hospital-text-3">الخدمات وأسعارها</p>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 rounded-lg border border-hospital-primary/30 bg-hospital-primary-pale px-3 py-1.5 text-xs font-semibold text-hospital-primary hover:bg-hospital-primary/10 transition-colors"
                            @click="addServiceRow"
                        >
                            <Plus class="h-3.5 w-3.5" />
                            إضافة خدمة
                        </button>
                    </div>

                    <div v-if="form.items.length === 0" class="rounded-xl border border-dashed border-hospital-border bg-gray-50/60 py-8 text-center text-sm text-hospital-text-3">
                        لا توجد خدمات — اضغط "إضافة خدمة" لبدء التسعير
                    </div>

                    <div v-else class="overflow-hidden rounded-xl border border-hospital-border">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-hospital-text-3">الخدمة</th>
                                    <th class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-hospital-text-3 w-36">السعر (ج)</th>
                                    <th class="w-10 px-3 py-2.5" />
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-hospital-border">
                                <tr v-for="(item, idx) in form.items" :key="idx" class="bg-white">
                                    <td class="px-3 py-2">
                                        <select v-model="item.service_id" class="fi" @change="onServiceSelect(idx)">
                                            <option value="">— اختر خدمة —</option>
                                            <option v-for="svc in services" :key="svc.id" :value="svc.id">
                                                {{ svc.name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="item.price" class="fi text-center tabular-nums" type="number" min="0" step="0.01" placeholder="0.00" />
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button
                                            type="button"
                                            class="rounded-lg p-1.5 text-hospital-text-3 hover:bg-hospital-danger-pale hover:text-hospital-danger transition-colors"
                                            @click="removeServiceRow(idx)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="button" class="fbtn-secondary" @click="showModal = false">إلغاء</button>
                    <button type="submit" class="fbtn-primary" :disabled="form.processing">
                        {{ form.processing ? 'جارٍ الحفظ...' : 'إنشاء القائمة' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<style scoped>
.fi {
    width: 100%;
    padding: 8px 11px;
    border: 1.5px solid var(--color-hospital-border);
    border-radius: 8px;
    font-size: 13px;
    font-family: inherit;
    color: var(--color-hospital-text);
    background: #fff;
    direction: rtl;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.fi:focus {
    outline: none;
    border-color: var(--color-hospital-primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-hospital-primary) 15%, transparent);
}
.fi-err {
    border-color: var(--color-hospital-danger);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-hospital-danger) 15%, transparent);
}
.fg {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.fg label {
    font-size: 11px;
    font-weight: 700;
    color: var(--color-hospital-text-2);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.form-section {
    border: 1.5px solid var(--color-hospital-border);
    border-radius: 10px;
    padding: 14px;
    background: var(--color-hospital-surface-2);
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.form-section-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--color-hospital-primary);
    margin-bottom: 2px;
}
.form-err-msg {
    font-size: 11px;
    color: var(--color-hospital-danger);
    margin-top: 2px;
}
.pct-badge {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    font-weight: 800;
    pointer-events: none;
}
.form-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding-top: 16px;
    border-top: 1.5px solid var(--color-hospital-border);
}
.fbtn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    background: var(--color-hospital-primary);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
}
.fbtn-primary:hover { background: var(--color-hospital-primary-light); }
.fbtn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.fbtn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    font-family: inherit;
    background: #fff;
    color: var(--color-hospital-text-2);
    border: 1.5px solid var(--color-hospital-border);
    cursor: pointer;
    transition: background 0.15s;
}
.fbtn-secondary:hover { background: var(--color-hospital-bg); }
</style>
