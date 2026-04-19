<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Shield } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import Modal from '@/components/shared/Modal.vue';

defineOptions({ layout: AppLayout });

interface Role {
    id: number;
    name: string;
    permissions: { name: string }[];
}

interface Permission {
    name: string;
}

const props = defineProps<{
    roles: Role[];
    allPermissions: Permission[];
}>();

const roleLabels: Record<string, string> = {
    admin:      'مدير النظام',
    doctor:     'طبيب',
    receptionist: 'استقبال',
    accountant: 'محاسب',
    nurse:      'ممرض / مساعد',
    storekeeper: 'مخزن',
};

// Group permissions by prefix
const permissionGroups = computed(() => {
    const groups: Record<string, string[]> = {};

    for (const p of props.allPermissions) {
        const group = p.name.split('.')[0];

        if (!groups[group]) {
 groups[group] = []; 
}

        groups[group].push(p.name);
    }

    return groups;
});

const groupLabels: Record<string, string> = {
    dashboard:  'لوحة التحكم',
    booking:    'الحجوزات',
    clinic:     'العيادة',
    labs:       'الفحوصات',
    surgery:    'العمليات',
    lasik:      'الليزك',
    laser:      'الليزر',
    treasury:   'الخزنة',
    journal:    'قيود اليومية',
    reports:    'التقارير',
    doctors:    'الأطباء',
    drpayments: 'مستحقات الأطباء',
    services:   'الخدمات',
    inventory:  'المخزن',
    insurance:  'التأمين',
    users:      'المستخدمون',
    settings:   'الإعدادات',
    hide_amounts: 'إخفاء المبالغ',
};

// Edit permissions for a role
const editingRole  = ref<Role | null>(null);
const editForm     = useForm({ permissions: [] as string[] });

function openEdit(role: Role) {
    editingRole.value    = role;
    editForm.permissions = role.permissions.map(p => p.name);
}

function togglePermission(perm: string) {
    const idx = editForm.permissions.indexOf(perm);

    if (idx === -1) {
        editForm.permissions.push(perm);
    } else {
        editForm.permissions.splice(idx, 1);
    }
}

function submitEdit() {
    if (!editingRole.value) {
 return; 
}

    editForm.put(`/roles/${editingRole.value.id}/permissions`, {
        onSuccess: () => {
 editingRole.value = null; 
},
    });
}
</script>

<template>
    <Head title="الأدوار والصلاحيات" />

    <h2 class="mb-5 text-lg font-bold text-hospital-text">الأدوار والصلاحيات</h2>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
            v-for="role in roles"
            :key="role.id"
            class="overflow-hidden rounded-xl border border-hospital-border bg-white shadow-sm"
        >
            <!-- Role Header -->
            <div class="flex items-center justify-between border-b border-hospital-border bg-hospital-bg px-4 py-3">
                <div class="flex items-center gap-2">
                    <Shield class="h-4 w-4 text-hospital-primary" />
                    <span class="font-semibold text-hospital-text">{{ roleLabels[role.name] ?? role.name }}</span>
                </div>
                <span class="text-xs text-hospital-muted">{{ role.permissions.length }} صلاحية</span>
            </div>

            <!-- Permissions list -->
            <div class="p-4">
                <div class="flex flex-wrap gap-1.5">
                    <span
                        v-for="perm in role.permissions"
                        :key="perm.name"
                        class="rounded-full bg-hospital-primary/10 px-2 py-0.5 text-xs text-hospital-primary"
                    >
                        {{ perm.name }}
                    </span>
                    <span v-if="role.permissions.length === 0" class="text-xs text-hospital-muted">لا توجد صلاحيات</span>
                </div>
                <button
                    v-if="role.name !== 'admin'"
                    class="mt-3 w-full rounded-lg border border-hospital-border py-1.5 text-xs text-hospital-text hover:bg-hospital-bg transition-colors"
                    @click="openEdit(role)"
                >
                    تعديل الصلاحيات
                </button>
                <p v-else class="mt-3 text-center text-xs text-hospital-muted">صلاحيات كاملة — لا يمكن تعديلها</p>
            </div>
        </div>
    </div>

    <!-- Edit Permissions Modal -->
    <Modal v-if="editingRole" :model-value="!!editingRole" title="تعديل صلاحيات الدور" size="lg" @update:model-value="editingRole = null">
        <form class="space-y-5" @submit.prevent="submitEdit">
            <p class="text-sm font-medium text-hospital-text">
                دور: <span class="text-hospital-primary">{{ roleLabels[editingRole.name] ?? editingRole.name }}</span>
            </p>

            <div
                v-for="(perms, group) in permissionGroups"
                :key="group"
                class="rounded-lg border border-hospital-border p-3"
            >
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-hospital-muted">
                    {{ groupLabels[group] ?? group }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <label
                        v-for="perm in perms"
                        :key="perm"
                        class="flex cursor-pointer items-center gap-1.5 rounded-lg border px-2 py-1 text-xs transition-colors"
                        :class="editForm.permissions.includes(perm)
                            ? 'border-hospital-primary bg-hospital-primary/10 text-hospital-primary'
                            : 'border-hospital-border text-hospital-muted hover:border-hospital-primary/30'"
                    >
                        <input
                            type="checkbox"
                            :checked="editForm.permissions.includes(perm)"
                            class="sr-only"
                            @change="togglePermission(perm)"
                        />
                        {{ perm.split('.')[1] ?? perm }}
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-hospital-border pt-4">
                <button type="button" class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="editingRole = null">إلغاء</button>
                <button type="submit" :disabled="editForm.processing" class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60">حفظ</button>
            </div>
        </form>
    </Modal>
</template>
