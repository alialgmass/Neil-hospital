<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue'
import Badge from '@/components/shared/Badge.vue'
import Modal from '@/components/shared/Modal.vue'
import { router, useForm } from '@inertiajs/vue3'
import { Edit2 } from 'lucide-vue-next'
import { ref } from 'vue'

defineOptions({ layout: AppLayout })

interface Company {
    id: string
    name: string
    code?: string
    phone?: string
    coverage_pct: number
    disc_pct: number
    contact_person?: string
    email?: string
    status: 'active' | 'inactive'
    contract_no?: string
}

const props = defineProps<{
    companies: { data: Company[]; links: unknown[] }
    filters: { search?: string }
}>()

const showModal = ref(false)
const editingCompany = ref<Company | null>(null)

const form = useForm({
    name: '',
    code: '',
    phone: '',
    address: '',
    contract_no: '',
    coverage_pct: 80,
    disc_pct: 0,
    contact_person: '',
    email: '',
    status: 'active' as 'active' | 'inactive',
    notes: '',
})

const searchQuery = ref(props.filters.search ?? '')

function search() {
    router.get('/insurance', { search: searchQuery.value }, { preserveState: true, replace: true })
}

function openCreate() {
    editingCompany.value = null
    form.reset()
    showModal.value = true
}

function openEdit(company: Company) {
    editingCompany.value = company
    Object.assign(form, {
        name: company.name,
        code: company.code ?? '',
        phone: company.phone ?? '',
        coverage_pct: company.coverage_pct,
        disc_pct: company.disc_pct,
        contact_person: company.contact_person ?? '',
        email: company.email ?? '',
        status: company.status,
        contract_no: company.contract_no ?? '',
    })
    showModal.value = true
}

function submit() {
    if (editingCompany.value) {
        form.put(`/insurance/${editingCompany.value.id}`, { onSuccess: () => { showModal.value = false } })
    } else {
        form.post('/insurance', { onSuccess: () => { showModal.value = false } })
    }
}
</script>

<template>
    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">شركات التأمين</h1>
            <button class="btn-primary" @click="openCreate">+ إضافة شركة</button>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <input
                v-model="searchQuery"
                class="input-field w-72"
                type="text"
                placeholder="بحث باسم الشركة..."
                @input="search"
            />
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الشركة</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الكود</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الهاتف</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">نسبة التغطية</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">نسبة الخصم</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الحالة</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="company in companies.data"
                        :key="company.id"
                        class="border-t border-gray-100 hover:bg-gray-50"
                    >
                        <td class="px-4 py-3 font-medium">{{ company.name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ company.code || '—' }}</td>
                        <td class="px-4 py-3">{{ company.phone || '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-blue-700">{{ company.coverage_pct }}%</span>
                        </td>
                        <td class="px-4 py-3">{{ company.disc_pct }}%</td>
                        <td class="px-4 py-3">
                            <Badge :variant="(company.status === 'active' ? 'active' : 'cancelled')">
                                {{ company.status === 'active' ? 'نشط' : 'غير نشط' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <button class="text-gray-400 hover:text-blue-600" @click="openEdit(company)">
                                <Edit2 class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="companies.data.length === 0">
                        <td class="px-4 py-8 text-center text-gray-400" colspan="7">لا توجد شركات تأمين</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create / Edit Modal -->
        <Modal :show="showModal" :title="editingCompany ? 'تعديل شركة التأمين' : 'إضافة شركة تأمين'" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="mb-1 block text-sm font-medium">اسم الشركة *</label>
                        <input v-model="form.name" class="input-field" type="text" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">الكود</label>
                        <input v-model="form.code" class="input-field" type="text" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">رقم العقد</label>
                        <input v-model="form.contract_no" class="input-field" type="text" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">الهاتف</label>
                        <input v-model="form.phone" class="input-field" type="text" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">المسؤول</label>
                        <input v-model="form.contact_person" class="input-field" type="text" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">نسبة التغطية %</label>
                        <input v-model.number="form.coverage_pct" class="input-field" type="number" min="0" max="100" step="0.01" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">نسبة الخصم %</label>
                        <input v-model.number="form.disc_pct" class="input-field" type="number" min="0" max="100" step="0.01" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">الحالة</label>
                        <select v-model="form.status" class="input-field">
                            <option value="active">نشط</option>
                            <option value="inactive">غير نشط</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">البريد الإلكتروني</label>
                        <input v-model="form.email" class="input-field" type="email" />
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-sm font-medium">ملاحظات</label>
                        <textarea v-model="form.notes" class="input-field" rows="2" />
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="btn-secondary" @click="showModal = false">إلغاء</button>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'جارٍ الحفظ...' : 'حفظ' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>
