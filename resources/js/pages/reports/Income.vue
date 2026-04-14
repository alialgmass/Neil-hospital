<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { TrendingUp, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import StatCard from '@/components/shared/StatCard.vue';

interface RevenueRow {
    dept?: string;
    doctor_name?: string;
    cases: number;
    revenue: number;
}

const props = defineProps<{
    from: string;
    to: string;
    revenueByDept: RevenueRow[];
    revenueByDoc: RevenueRow[];
}>();

const fromFilter = ref(props.from);
const toFilter   = ref(props.to);

function applyFilters() {
    router.get('/reports/income', { from: fromFilter.value, to: toFilter.value }, { preserveState: true });
}

const deptLabels: Record<string, string> = {
    clinic: 'العيادة',
    labs: 'الفحوصات',
    surgery: 'العمليات',
    lasik: 'الليزك',
    laser: 'الليزر',
};

const totalRevenue = props.revenueByDept.reduce((s, r) => s + Number(r.revenue), 0);
const totalCases   = props.revenueByDept.reduce((s, r) => s + Number(r.cases), 0);

function fmt(n: number) {
    return Number(n).toLocaleString('ar-EG');
}

function pct(revenue: number) {
    if (totalRevenue === 0) {
 return '0'; 
}

    return ((revenue / totalRevenue) * 100).toFixed(1);
}
</script>

<template>
    <Head title="تقرير الإيرادات" />

    <!-- Filters -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold text-hospital-text">تقرير الإيرادات</h2>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-hospital-muted">من</span>
            <input
                v-model="fromFilter"
                type="date"
                class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
            />
            <span class="text-sm text-hospital-muted">إلى</span>
            <input
                v-model="toFilter"
                type="date"
                class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none"
            />
            <button
                class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-medium text-white hover:bg-hospital-primary/90 transition-colors"
                @click="applyFilters"
            >
                عرض
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <StatCard label="إجمالي الإيرادات" :value="`${fmt(totalRevenue)} ج.م`" color="primary">
            <template #icon><TrendingUp class="h-5 w-5" /></template>
        </StatCard>
        <StatCard label="إجمالي الحالات" :value="totalCases.toString()" color="success">
            <template #icon><Users class="h-5 w-5" /></template>
        </StatCard>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Revenue by Department -->
        <div class="rounded-xl border border-hospital-border bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-hospital-text">الإيرادات حسب القسم</h3>
            <div v-if="revenueByDept.length === 0" class="py-8 text-center text-sm text-hospital-muted">
                لا توجد إيرادات في هذه الفترة
            </div>
            <template v-else>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-hospital-border text-right text-xs text-hospital-muted">
                            <th class="pb-2">القسم</th>
                            <th class="pb-2 text-center">الحالات</th>
                            <th class="pb-2 text-center">النسبة</th>
                            <th class="pb-2 text-left">الإيراد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hospital-border/50">
                        <tr v-for="row in revenueByDept" :key="row.dept" class="hover:bg-hospital-bg/50">
                            <td class="py-2.5">
                                <span class="font-medium">{{ deptLabels[row.dept!] ?? row.dept }}</span>
                            </td>
                            <td class="py-2.5 text-center text-hospital-muted">{{ row.cases }}</td>
                            <td class="py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-hospital-border">
                                        <div class="h-full rounded-full bg-hospital-primary" :style="{ width: pct(row.revenue) + '%' }" />
                                    </div>
                                    <span class="text-xs text-hospital-muted">{{ pct(row.revenue) }}%</span>
                                </div>
                            </td>
                            <td class="py-2.5 text-left font-mono text-hospital-success">{{ fmt(row.revenue) }} ج.م</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-hospital-border font-bold">
                            <td class="pt-2">الإجمالي</td>
                            <td class="pt-2 text-center">{{ totalCases }}</td>
                            <td class="pt-2 text-center text-hospital-muted">100%</td>
                            <td class="pt-2 text-left font-mono text-hospital-primary">{{ fmt(totalRevenue) }} ج.م</td>
                        </tr>
                    </tfoot>
                </table>
            </template>
        </div>

        <!-- Revenue by Doctor -->
        <div class="rounded-xl border border-hospital-border bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-hospital-text">الإيرادات حسب الطبيب</h3>
            <div v-if="revenueByDoc.length === 0" class="py-8 text-center text-sm text-hospital-muted">
                لا توجد إيرادات في هذه الفترة
            </div>
            <template v-else>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-hospital-border text-right text-xs text-hospital-muted">
                            <th class="pb-2">الطبيب</th>
                            <th class="pb-2 text-center">الحالات</th>
                            <th class="pb-2 text-center">النسبة</th>
                            <th class="pb-2 text-left">الإيراد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hospital-border/50">
                        <tr v-for="row in revenueByDoc" :key="row.doctor_name" class="hover:bg-hospital-bg/50">
                            <td class="py-2.5 font-medium">{{ row.doctor_name }}</td>
                            <td class="py-2.5 text-center text-hospital-muted">{{ row.cases }}</td>
                            <td class="py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-hospital-border">
                                        <div class="h-full rounded-full bg-hospital-accent" :style="{ width: pct(row.revenue) + '%' }" />
                                    </div>
                                    <span class="text-xs text-hospital-muted">{{ pct(row.revenue) }}%</span>
                                </div>
                            </td>
                            <td class="py-2.5 text-left font-mono text-hospital-success">{{ fmt(row.revenue) }} ج.م</td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </div>
    </div>
</template>
