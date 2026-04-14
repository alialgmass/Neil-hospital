<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { PlusCircle, Pencil } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Modal from '@/components/shared/Modal.vue';

interface Account {
    id: string;
    code: string;
    name: string;
    group: string;
    nature: string;
    parent_id?: string;
    balance: number;
    is_active: boolean;
}

const props = defineProps<{
    accounts: Account[];
}>();

const groupLabels: Record<string, string> = {
    assets:      'أصول',
    liabilities: 'خصوم',
    equity:      'حقوق ملكية',
    revenues:    'إيرادات',
    expenses:    'مصروفات',
};
const natureLabels: Record<string, string> = {
    debit:  'مدين',
    credit: 'دائن',
};

// Group accounts by group for display
const groups = ['assets', 'liabilities', 'equity', 'revenues', 'expenses'] as const;
const byGroup = computed(() => {
    const map: Record<string, Account[]> = {};
    groups.forEach(g => {
 map[g] = []; 
});
    props.accounts.forEach(a => {
        if (map[a.group]) {
 map[a.group].push(a); 
}
    });

    return map;
});

// Parent accounts for selector
const parentOptions = computed(() => props.accounts.filter(a => a.is_active));

function fmt(n: number) {
    return Number(n).toLocaleString('ar-EG', { minimumFractionDigits: 2 });
}

// Add form
const showAdd = ref(false);
const addForm = useForm({
    code:      '',
    name:      '',
    group:     'assets' as string,
    nature:    'debit' as string,
    parent_id: '',
});
function submitAdd() {
    addForm.post('/accounts', {
        onSuccess: () => {
 showAdd.value = false; addForm.reset(); 
},
    });
}

// Edit form
const showEdit   = ref(false);
const editTarget = ref<Account | null>(null);
const editForm   = useForm({
    name:      '',
    group:     'assets' as string,
    nature:    'debit' as string,
    parent_id: '',
    is_active: true,
});
function openEdit(account: Account) {
    editTarget.value  = account;
    editForm.name      = account.name;
    editForm.group     = account.group;
    editForm.nature    = account.nature;
    editForm.parent_id = account.parent_id ?? '';
    editForm.is_active = account.is_active;
    showEdit.value = true;
}
function submitEdit() {
    if (!editTarget.value) {
 return; 
}

    editForm.put(`/accounts/${editTarget.value.id}`, {
        onSuccess: () => {
 showEdit.value = false; 
},
    });
}
</script>

<template>
    <Head title="الدليل المحاسبي" />

    <!-- Header -->
    <div class="mb-5 flex items-center justify-between">
        <h2 class="text-lg font-bold text-hospital-text">الدليل المحاسبي</h2>
        <button
            class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90 transition-colors"
            @click="showAdd = true"
        >
            <PlusCircle class="h-4 w-4" /> حساب جديد
        </button>
    </div>

    <!-- Accounts by Group -->
    <div class="space-y-5">
        <div
            v-for="group in groups"
            :key="group"
            class="overflow-hidden rounded-xl border border-hospital-border bg-white shadow-sm"
        >
            <div class="border-b border-hospital-border bg-hospital-bg px-4 py-3">
                <h3 class="font-semibold text-hospital-text">{{ groupLabels[group] }}</h3>
            </div>
            <div v-if="byGroup[group].length === 0" class="px-4 py-4 text-sm text-hospital-muted">
                لا توجد حسابات في هذا القسم
            </div>
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b border-hospital-border/50 text-right text-xs text-hospital-muted">
                        <th class="px-4 py-2.5">الكود</th>
                        <th class="px-4 py-2.5">اسم الحساب</th>
                        <th class="px-4 py-2.5 text-center">الطبيعة</th>
                        <th class="px-4 py-2.5 text-center">الحالة</th>
                        <th class="px-4 py-2.5 text-left">الرصيد</th>
                        <th class="px-4 py-2.5" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-hospital-border/40">
                    <tr
                        v-for="account in byGroup[group]"
                        :key="account.id"
                        class="hover:bg-hospital-bg/50"
                        :class="{ 'opacity-50': !account.is_active }"
                    >
                        <td class="px-4 py-2.5 font-mono text-xs text-hospital-muted">{{ account.code }}</td>
                        <td class="px-4 py-2.5 font-medium">{{ account.name }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="account.nature === 'debit' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600'"
                            >
                                {{ natureLabels[account.nature] }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="account.is_active ? 'bg-hospital-success/10 text-hospital-success' : 'bg-hospital-muted/20 text-hospital-muted'"
                            >
                                {{ account.is_active ? 'نشط' : 'متوقف' }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-left font-mono">
                            <span :class="account.balance >= 0 ? 'text-hospital-text' : 'text-hospital-danger'">
                                {{ fmt(account.balance) }} ج.م
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-left">
                            <button
                                class="rounded p-1.5 text-hospital-muted hover:bg-hospital-bg hover:text-hospital-primary transition-colors"
                                @click="openEdit(account)"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <Modal v-model="showAdd" title="إضافة حساب جديد" size="sm">
        <form class="space-y-4" @submit.prevent="submitAdd">
            <div>
                <label class="mb-1 block text-sm font-medium">كود الحساب <span class="text-hospital-danger">*</span></label>
                <input v-model="addForm.code" type="text" placeholder="مثال: 1010" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                <p v-if="addForm.errors.code" class="mt-1 text-xs text-hospital-danger">{{ addForm.errors.code }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">اسم الحساب <span class="text-hospital-danger">*</span></label>
                <input v-model="addForm.name" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
                <p v-if="addForm.errors.name" class="mt-1 text-xs text-hospital-danger">{{ addForm.errors.name }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">المجموعة</label>
                    <select v-model="addForm.group" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option v-for="(label, key) in groupLabels" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الطبيعة</label>
                    <select v-model="addForm.nature" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="debit">مدين</option>
                        <option value="credit">دائن</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">الحساب الأب (اختياري)</label>
                <select v-model="addForm.parent_id" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                    <option value="">— بدون حساب أب —</option>
                    <option v-for="a in parentOptions" :key="a.id" :value="a.id">{{ a.code }} — {{ a.name }}</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showAdd = false">إلغاء</button>
                <button type="submit" :disabled="addForm.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60">إضافة</button>
            </div>
        </form>
    </Modal>

    <!-- Edit Modal -->
    <Modal v-model="showEdit" title="تعديل الحساب" size="sm">
        <form class="space-y-4" @submit.prevent="submitEdit">
            <div>
                <label class="mb-1 block text-sm font-medium">اسم الحساب <span class="text-hospital-danger">*</span></label>
                <input v-model="editForm.name" type="text" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">المجموعة</label>
                    <select v-model="editForm.group" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option v-for="(label, key) in groupLabels" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">الطبيعة</label>
                    <select v-model="editForm.nature" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                        <option value="debit">مدين</option>
                        <option value="credit">دائن</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">الحساب الأب (اختياري)</label>
                <select v-model="editForm.parent_id" class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none">
                    <option value="">— بدون حساب أب —</option>
                    <option v-for="a in parentOptions" :key="a.id" :value="a.id">{{ a.code }} — {{ a.name }}</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input id="is_active" v-model="editForm.is_active" type="checkbox" class="rounded border-hospital-border text-hospital-primary focus:ring-hospital-primary" />
                <label for="is_active" class="text-sm">حساب نشط</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="showEdit = false">إلغاء</button>
                <button type="submit" :disabled="editForm.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60">حفظ</button>
            </div>
        </form>
    </Modal>
</template>
