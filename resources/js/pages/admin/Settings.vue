<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { ref } from 'vue';

interface Setting {
    key: string;
    value: string;
    group: string;
    label?: string;
}

const props = defineProps<{
    settings: Record<string, Setting>;
}>();

const form = ref<Record<string, string>>({});
Object.values(props.settings).forEach(s => {
 form.value[s.key] = s.value ?? ''; 
});

function submit() {
    const payload = Object.entries(form.value).map(([key, value]) => ({ key, value }));
    router.post('/settings', { settings: payload });
}

const groupLabels: Record<string, string> = {
    hospital: 'معلومات المستشفى',
    system:   'إعدادات النظام',
    billing:  'إعدادات الفواتير',
};

const settingLabels: Record<string, string> = {
    hospital_name:      'اسم المستشفى',
    hospital_specialty: 'التخصص',
    hospital_address:   'العنوان',
    hospital_phone:     'الهاتف',
    mrn_format:         'تنسيق رقم الملف',
    default_currency:   'العملة',
};

// Group settings by group field
const grouped = Object.values(props.settings).reduce((acc, s) => {
    const g = s.group ?? 'system';

    if (!acc[g]) {
 acc[g] = []; 
}

    acc[g].push(s);

    return acc;
}, {} as Record<string, Setting[]>);
</script>

<template>
    <Head title="إعدادات النظام" />

    <div class="mb-5 flex items-center justify-between">
        <h2 class="text-lg font-bold text-hospital-text">إعدادات النظام</h2>
        <button
            class="flex items-center gap-1.5 rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90"
            @click="submit"
        >
            <Save class="h-4 w-4" />
            حفظ الإعدادات
        </button>
    </div>

    <div class="space-y-6">
        <div v-for="(groupSettings, groupKey) in grouped" :key="groupKey" class="rounded-xl border border-hospital-border bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-semibold text-hospital-text">{{ groupLabels[groupKey] ?? groupKey }}</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div v-for="setting in groupSettings" :key="setting.key">
                    <label class="mb-1 block text-sm font-medium text-hospital-text">
                        {{ settingLabels[setting.key] ?? setting.key }}
                    </label>
                    <input
                        v-model="form[setting.key]"
                        type="text"
                        class="w-full rounded-lg border border-hospital-border px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
