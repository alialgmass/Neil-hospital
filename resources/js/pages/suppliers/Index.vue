<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { PlusCircle } from 'lucide-vue-next';
import Badge from '@/components/shared/Badge.vue';
import DataTable from '@/components/shared/DataTable.vue';
import Modal from '@/components/shared/Modal.vue';
import SearchBar from '@/components/shared/SearchBar.vue';

interface Supplier {
    id: string;
    name: string;
    phone?: string;
    email?: string;
    balance: number;
    is_active: boolean;
}

const props = defineProps<{
    suppliers: { data: Supplier[]; current_page: number; last_page: number; total: number };
    filters: { search?: string };
}>();

const columns = [
    { key: 'name',      label: 'الاسم',   sortable: true },
    { key: 'phone',     label: 'الهاتف' },
    { key: 'email',     label: 'البريد' },
    { key: 'balance',   label: 'الرصيد' },
    { key: 'is_active', label: 'الحالة' },
];

const search = ref(props.filters.search ?? '');
function applySearch() { router.get('/suppliers', { search: search.value || undefined }, { preserveState: true }); }
function goToPage(page: number) { router.get('/suppliers', { search: search.value || undefined, page }, { preserveState: true }); }

const showAdd = ref(false);
const form = useForm({ name: '', phone: '', email: '', address: '', tax_no: '' });
function submit() { form.post('/suppliers', { onSuccess: () => { showAdd.value = false; form.reset(); } }); }
</script>

<template>
    <Head title="الموردون" />

    <div class="mb-5 flex items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-hospital-text">إدارة الموردين</h2>
        <div class="flex items-center gap-2">
            <SearchBar v-model="search" placeholder="بحث بالاسم..." @update:model-value="applySearch" />
            <button class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90" @click="showAdd = true">
                <PlusCircle class="h-4 w-4" /> مورد جديد
            </button>
        </div>
    </div>

    <DataTable :columns="columns" :rows="suppliers.data" :current-page="suppliers.current_page" :last-page="suppliers.last_page" :total="suppliers.total" empty-text="لا يوجد موردون" @page="goToPage">
        <template #cell-balance="{ value }">
            <span class="font-mono">{{ Number(value).toLocaleString('ar-EG') }} ج.م</span>
        </template>
        <template #cell-is_active="{ value }">
            <Badge :variant="value ? 'active' : 'inactive'" />
        </template>
    </DataTable>

    <Modal v-model="showAdd" title="إضافة مورد جديد" size="md">
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium">اسم المورد</label>
                <input v-model="form.name" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-hospital-danger">{{ form.errors.name }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">الهاتف</label>
                    <input v-model="form.phone" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">البريد الإلكتروني</label>
                    <input v-model="form.email" type="email" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">العنوان</label>
                <textarea v-model="form.address" rows="2" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showAdd = false">إلغاء</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60">إضافة</button>
            </div>
        </form>
    </Modal>
</template>
