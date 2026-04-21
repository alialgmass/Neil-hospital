<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Edit2, Upload, Trash2, ToggleRight, Package } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/components/layout/AppLayout.vue';
import Badge from '@/components/shared/Badge.vue';
import Modal from '@/components/shared/Modal.vue';

defineOptions({ layout: AppLayout });

interface Service {
    id: string;
    name: string;
    dept: string;
    price: number;
    ins_price: number;
    center_type: 'pct' | 'fixed';
    center_val: number;
    duration_mins: number;
    status: 'active' | 'inactive';
}

const props = defineProps<{
    services: { data: Service[]; links: unknown[] };
    filters: { search?: string; dept?: string; status?: string };
}>();

const showModal = ref(false);
const editingService = ref<Service | null>(null);
const showDeleteModal = ref(false);
const deletingService = ref<Service | null>(null);

const form = useForm({
    name: '',
    dept: 'clinic' as Service['dept'],
    price: 0,
    ins_price: 0,
    center_type: 'pct' as 'pct' | 'fixed',
    center_val: 0,
    duration_mins: 30,
    status: 'active' as 'active' | 'inactive',
});

const importForm = useForm({ file: null as File | null });
const showImportModal = ref(false);

const deleteForm = useForm({});

const deptLabels: Record<string, string> = {
    clinic: 'العيادة',
    labs: 'الفحوصات',
    surgery: 'العمليات',
    lasik: 'الليزك',
    laser: 'الليزر',
};

const filters = ref({ ...props.filters });
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(filters, () => {
    if (searchTimeout) {
clearTimeout(searchTimeout);
}

    searchTimeout = setTimeout(() => {
        router.get('/services', filters.value, {
            preserveState: true,
            replace: true,
        });
    }, 300);
}, { deep: true });

function openCreate() {
    editingService.value = null;
    form.reset();
    showModal.value = true;
}

function openEdit(svc: Service) {
    editingService.value = svc;
    form.name = svc.name;
    form.dept = svc.dept as Service['dept'];
    form.price = svc.price;
    form.ins_price = svc.ins_price;
    form.center_type = svc.center_type;
    form.center_val = svc.center_val;
    form.duration_mins = svc.duration_mins;
    form.status = svc.status;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    form.clearErrors();
}

function submit() {
    if (editingService.value) {
        form.put(`/services/${editingService.value.id}`, {
            onSuccess: () => {
                closeModal();
                toast.success('تم تحديث الخدمة بنجاح');
            },
        });
    } else {
        form.post('/services', {
            onSuccess: () => {
                closeModal();
                toast.success('تم إضافة الخدمة بنجاح');
            },
        });
    }
}

function submitImport() {
    importForm.post('/services/import', {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
            toast.success('تم استيراد الخدمات بنجاح');
        },
    });
}

function confirmDelete(svc: Service) {
    deletingService.value = svc;
    showDeleteModal.value = true;
}

function deleteService() {
    if (!deletingService.value) {
return;
}

    deleteForm.delete(`/services/${deletingService.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingService.value = null;
            toast.success('تم حذف الخدمة بنجاح');
        },
    });
}

function toggleStatus(svc: Service) {
    const newStatus = svc.status === 'active' ? 'inactive' : 'active';
    router.patch(`/services/${svc.id}/status`, { status: newStatus }, {
        onSuccess: () => {
            toast.success(newStatus === 'active' ? 'تم تفعيل الخدمة' : 'تم إلغاء تفعيل الخدمة');
        },
    });
}

function centerSharePreview(): string {
    if (form.center_type === 'pct') {
        return `${((form.price * form.center_val) / 100).toFixed(2)} ج (${form.center_val}%)`;
    }

    return `${form.center_val} ج (ثابت)`;
}

function drSharePreview(): number {
    if (form.center_type === 'pct') {
        return form.price - (form.price * form.center_val) / 100;
    }

    return form.price - form.center_val;
}
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">
                إدارة الخدمات والأسعار
            </h1>
            <div class="flex gap-3">
                <button
                    class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                    @click="showImportModal = true"
                >
                    <Upload class="h-4 w-4" />
                    استيراد Excel
                </button>
                <button
                    class="flex items-center gap-2 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-hospital-primary-light"
                    @click="openCreate"
                >
                    + إضافة خدمة
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap gap-3">
            <div class="relative">
                <input
                    v-model="filters.search"
                    class="input-field w-64 pl-10"
                    type="text"
                    placeholder="بحث باسم الخدمة..."
                />
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <select v-model="filters.dept" class="input-field">
                <option value="">كل الأقسام</option>
                <option v-for="(label, key) in deptLabels" :key="key" :value="key">
                    {{ label }}
                </option>
            </select>
            <select v-model="filters.status" class="input-field">
                <option value="">كل الحالات</option>
                <option value="active">نشط</option>
                <option value="inactive">غير نشط</option>
            </select>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">الخدمة</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">القسم</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">السعر</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">سعر التأمين</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">حصة المركز</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">حصة الطبيب</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">الحالة</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="svc in services.data"
                            :key="svc.id"
                            class="border-t border-gray-100 transition-colors hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 font-medium">{{ svc.name }}</td>
                            <td class="px-4 py-3">
                                <Badge variant="info">{{ deptLabels[svc.dept] || svc.dept }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-left font-mono">{{ svc.price.toFixed(2) }} ج</td>
                            <td class="px-4 py-3 text-left font-mono text-blue-600">{{ svc.ins_price.toFixed(2) }} ج</td>
                            <td class="px-4 py-3 text-left font-mono text-gray-500">
                                {{ svc.center_type === 'pct' ? `${svc.center_val}%` : `${svc.center_val} ج` }}
                            </td>
                            <td class="px-4 py-3 text-left font-mono font-medium text-green-700">
                                {{
                                    (svc.center_type === 'pct'
                                        ? svc.price - (svc.price * svc.center_val) / 100
                                        : svc.price - svc.center_val
                                    ).toFixed(2)
                                }} ج
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="svc.status === 'active' ? 'active' : 'inactive'">
                                    {{ svc.status === 'active' ? 'نشط' : 'غير نشط' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        class="rounded p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                                        :title="svc.status === 'active' ? 'إلغاء التفعيل' : 'تفعيل'"
                                        @click="toggleStatus(svc)"
                                    >
                                        <ToggleRight v-if="svc.status === 'inactive'" class="h-5 w-5" />
                                        <ToggleRight v-else class="h-5 w-5 text-green-600" />
                                    </button>
                                    <button
                                        class="rounded p-1 text-gray-400 transition-colors hover:bg-blue-50 hover:text-blue-600"
                                        title="تعديل"
                                        @click="openEdit(svc)"
                                    >
                                        <Edit2 class="h-4 w-4" />
                                    </button>
                                    <button
                                        class="rounded p-1 text-gray-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                        title="حذف"
                                        @click="confirmDelete(svc)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="services.data.length === 0">
                            <td colspan="8" class="px-4 py-12">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <Package class="mb-3 h-16 w-16 opacity-50" />
                                    <p class="text-lg font-medium">لا توجد خدمات</p>
                                    <p class="text-sm">قم بإضافة خدمة جديدة للبدء</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal
            v-model="showModal"
            :title="editingService ? 'تعديل خدمة' : 'إضافة خدمة'"
            size="lg"
            @close="closeModal"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <!-- Basic Info Section -->
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">المعلومات الأساسية</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">اسم الخدمة *</label>
                            <input
                                v-model="form.name"
                                :class="['input-field w-full', form.errors.name && 'border-red-400 focus:border-red-500 focus:ring-red-500/20']"
                                type="text"
                                placeholder="مثال: فحص نظر شامل"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">القسم *</label>
                            <select
                                v-model="form.dept"
                                :class="['input-field w-full', form.errors.dept && 'border-red-400 focus:border-red-500 focus:ring-red-500/20']"
                                required
                            >
                                <option v-for="(label, key) in deptLabels" :key="key" :value="key">{{ label }}</option>
                            </select>
                            <p v-if="form.errors.dept" class="mt-1 text-xs text-red-500">{{ form.errors.dept }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">الحالة</label>
                            <select v-model="form.status" class="input-field w-full">
                                <option value="active">✅ نشط</option>
                                <option value="inactive">❌ غير نشط</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">مدة الخدمة (دقيقة)</label>
                            <input
                                v-model.number="form.duration_mins"
                                :class="['input-field w-full', form.errors.duration_mins && 'border-red-400 focus:border-red-500 focus:ring-red-500/20']"
                                type="number"
                                min="1"
                                placeholder="30"
                            />
                            <p v-if="form.errors.duration_mins" class="mt-1 text-xs text-red-500">{{ form.errors.duration_mins }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Section -->
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">الأسعار</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">السعر الأساسي (ج)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">ج</span>
                                <input
                                    v-model.number="form.price"
                                    :class="['input-field w-full pl-8', form.errors.price && 'border-red-400 focus:border-red-500 focus:ring-red-500/20']"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                            </div>
                            <p v-if="form.errors.price" class="mt-1 text-xs text-red-500">{{ form.errors.price }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">سعر التأمين (ج)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">ج</span>
                                <input
                                    v-model.number="form.ins_price"
                                    :class="['input-field w-full pl-8', form.errors.ins_price && 'border-red-400 focus:border-red-500 focus:ring-red-500/20']"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                            </div>
                            <p v-if="form.errors.ins_price" class="mt-1 text-xs text-red-500">{{ form.errors.ins_price }}</p>
                        </div>
                    </div>
                </div>

                <!-- Share Distribution Section -->
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">توزيع الإيرادات</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">نوع الحساب</label>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    :class="[
                                        'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-all',
                                        form.center_type === 'pct'
                                            ? 'border-hospital-primary bg-hospital-primary/10 text-hospital-primary'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                    ]"
                                    @click="form.center_type = 'pct'"
                                >
                                    نسبة مئوية %
                                </button>
                                <button
                                    type="button"
                                    :class="[
                                        'flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-all',
                                        form.center_type === 'fixed'
                                            ? 'border-hospital-primary bg-hospital-primary/10 text-hospital-primary'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                    ]"
                                    @click="form.center_type = 'fixed'"
                                >
                                    قيمة ثابتة
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">
                                {{ form.center_type === 'pct' ? 'نسبة المركز (%)' : 'قيمة المركز (ج)' }}
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    {{ form.center_type === 'pct' ? '%' : 'ج' }}
                                </span>
                                <input
                                    v-model.number="form.center_val"
                                    :class="['input-field w-full pl-8', form.errors.center_val && 'border-red-400 focus:border-red-500 focus:ring-red-500/20']"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    :placeholder="form.center_type === 'pct' ? '0' : '0.00'"
                                />
                            </div>
                            <p v-if="form.errors.center_val" class="mt-1 text-xs text-red-500">{{ form.errors.center_val }}</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div v-if="form.price > 0" class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <p class="mb-2 text-sm font-medium text-blue-800">💰 معاينة التوزيع</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="flex justify-between rounded bg-white/60 p-2">
                                <span class="text-gray-600">حصة المركز:</span>
                                <span class="font-medium text-blue-700">{{ centerSharePreview() }}</span>
                            </div>
                            <div class="flex justify-between rounded bg-white/60 p-2">
                                <span class="text-gray-600">مستحق الطبيب:</span>
                                <span class="font-medium text-green-700">{{ drSharePreview().toFixed(2) }} ج</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between border-t pt-4">
                    <button
                        type="button"
                        class="text-sm text-gray-500 hover:text-gray-700"
                        @click="closeModal"
                    >
                        إلغاء
                    </button>
                    <button
                        type="submit"
                        class="flex items-center gap-2 rounded-lg bg-hospital-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-hospital-primary-light hover:shadow-md disabled:opacity-60 disabled:shadow-none"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="animate-spin">⏳</span>
                        {{ form.processing ? 'جارٍ الحفظ...' : '💾 حفظ' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Import Modal -->
        <Modal v-model="showImportModal" title="استيراد الخدمات من Excel" @close="showImportModal = false">
            <form class="space-y-4" @submit.prevent="submitImport">
                <p class="rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                    الأعمدة المطلوبة: <code class="bg-white px-1">name, dept, price, center_val</code>
                </p>
                <div>
                    <label class="mb-1 block text-sm font-medium">ملف Excel / CSV</label>
                    <input
                        class="input-field"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        @change="(e: Event) => { importForm.file = (e.target as HTMLInputElement).files?.[0] ?? null; }"
                    />
                    <p v-if="importForm.errors.file" class="text-sm text-red-500">{{ importForm.errors.file }}</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50" @click="showImportModal = false">
                        إلغاء
                    </button>
                    <button type="submit" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-hospital-primary-light disabled:opacity-60" :disabled="importForm.processing">
                        {{ importForm.processing ? 'جارٍ الاستيراد...' : 'استيراد' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal v-model="showDeleteModal" title="تأكيد الحذف" @close="showDeleteModal = false">
            <div class="space-y-4">
                <p class="text-gray-700">
                    هل أنت متأكد من حذف الخدمة <strong class="text-gray-900">{{ deletingService?.name }}</strong>؟
                </p>
                <p class="text-sm text-red-600">لا يمكن التراجع عن هذا الإجراء</p>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50" @click="showDeleteModal = false">
                    إلغاء
                </button>
                <button type="button" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:opacity-60" :disabled="deleteForm.processing" @click="deleteService">
                    {{ deleteForm.processing ? 'جارٍ الحذف...' : 'حذف' }}
                </button>
            </div>
        </Modal>
    </div>
</template>