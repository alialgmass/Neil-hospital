<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface AccountRow {
    name: string;
    amount: number;
}

const props = defineProps<{
    data: {
        revenues: AccountRow[];
        expenses: AccountRow[];
        totalRevenue: number;
        totalExpense: number;
        netIncome: number;
        from: string;
        to: string;
    };
    filters: { from: string; to: string };
}>();

const from = ref(props.filters.from);
const to   = ref(props.filters.to);

function search() {
    router.get('/reports/profit-loss', { from: from.value, to: to.value }, { preserveState: true });
}

function exportExcel() {
    window.location.href = `/reports/profit-loss/export?from=${from.value}&to=${to.value}`;
}

function fmt(n: number) {
    return Number(n).toLocaleString('ar-EG', { minimumFractionDigits: 2 });
}
</script>

<template>
    <Head title="الأرباح والخسائر" />

    <!-- Filter row -->
    <div class="mb-5 flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-hospital-muted">من</label>
            <input v-model="from" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-hospital-muted">إلى</label>
            <input v-model="to" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" />
        </div>
        <button class="rounded-lg bg-hospital-primary px-4 py-2 text-sm font-semibold text-white" @click="search">🔍 حساب</button>
        <button class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="exportExcel">📊 Excel</button>
        <button class="rounded-lg border border-hospital-border px-4 py-2 text-sm hover:bg-hospital-bg" @click="() => window.print()">🖨️ طباعة</button>
    </div>

    <!-- Stats row -->
    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-green-100 bg-green-50 p-4 text-center">
            <p class="text-xs font-medium text-green-600">إجمالي الإيرادات</p>
            <p class="mt-1 text-2xl font-bold text-green-700">{{ fmt(data.totalRevenue) }}</p>
            <p class="text-xs text-green-500">جنيه</p>
        </div>
        <div class="rounded-xl border border-red-100 bg-red-50 p-4 text-center">
            <p class="text-xs font-medium text-red-600">إجمالي المصروفات</p>
            <p class="mt-1 text-2xl font-bold text-red-700">{{ fmt(data.totalExpense) }}</p>
            <p class="text-xs text-red-500">جنيه</p>
        </div>
        <div
            class="rounded-xl border p-4 text-center"
            :class="data.netIncome >= 0 ? 'border-blue-100 bg-blue-50' : 'border-red-100 bg-red-50'"
        >
            <p class="text-xs font-medium" :class="data.netIncome >= 0 ? 'text-blue-600' : 'text-red-600'">صافي الدخل</p>
            <p class="mt-1 text-2xl font-bold" :class="data.netIncome >= 0 ? 'text-blue-700' : 'text-red-700'">{{ fmt(data.netIncome) }}</p>
            <p class="text-xs" :class="data.netIncome >= 0 ? 'text-blue-500' : 'text-red-500'">{{ data.netIncome >= 0 ? 'ربح' : 'خسارة' }}</p>
        </div>
    </div>

    <!-- Revenues & Expenses 2-col -->
    <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <!-- Revenues -->
        <div class="overflow-hidden rounded-xl border border-hospital-border shadow-sm">
            <div class="border-b border-hospital-border bg-green-50 px-4 py-3">
                <p class="font-semibold text-green-800">📈 الإيرادات</p>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-hospital-border/50">
                    <tr v-for="(row, idx) in data.revenues" :key="idx" class="hover:bg-hospital-bg/40">
                        <td class="px-4 py-2.5 text-hospital-text">{{ row.name }}</td>
                        <td class="px-4 py-2.5 text-left font-mono font-medium text-green-700">{{ fmt(row.amount) }} ج.م</td>
                    </tr>
                    <tr v-if="data.revenues.length === 0">
                        <td colspan="2" class="px-4 py-4 text-center text-hospital-muted">لا توجد إيرادات</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-green-200 bg-green-50 font-bold">
                        <td class="px-4 py-3 text-green-800">إجمالي الإيرادات</td>
                        <td class="px-4 py-3 text-left font-mono text-green-800">{{ fmt(data.totalRevenue) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Expenses -->
        <div class="overflow-hidden rounded-xl border border-hospital-border shadow-sm">
            <div class="border-b border-hospital-border bg-red-50 px-4 py-3">
                <p class="font-semibold text-red-800">📉 المصروفات</p>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-hospital-border/50">
                    <tr v-for="(row, idx) in data.expenses" :key="idx" class="hover:bg-hospital-bg/40">
                        <td class="px-4 py-2.5 text-hospital-text">{{ row.name }}</td>
                        <td class="px-4 py-2.5 text-left font-mono font-medium text-red-700">{{ fmt(row.amount) }} ج.م</td>
                    </tr>
                    <tr v-if="data.expenses.length === 0">
                        <td colspan="2" class="px-4 py-4 text-center text-hospital-muted">لا توجد مصروفات</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-red-200 bg-red-50 font-bold">
                        <td class="px-4 py-3 text-red-800">إجمالي المصروفات</td>
                        <td class="px-4 py-3 text-left font-mono text-red-800">{{ fmt(data.totalExpense) }} ج.م</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Net Result Card -->
    <div class="overflow-hidden rounded-xl border border-hospital-border shadow-sm">
        <div class="px-4 py-3" style="background: linear-gradient(135deg, #072E63, #0A4FA6)">
            <p class="text-sm font-bold text-white">نتيجة الفترة المالية</p>
        </div>
        <div class="p-6 text-center">
            <p class="text-sm text-hospital-text-2">الفترة من {{ data.from }} إلى {{ data.to }}</p>
            <p
                class="mt-3 text-4xl font-bold"
                :class="data.netIncome >= 0 ? 'text-hospital-success' : 'text-hospital-danger'"
            >
                {{ fmt(data.netIncome) }} ج.م
            </p>
            <p
                class="mt-2 rounded-full px-4 py-1 text-sm font-semibold inline-block"
                :class="data.netIncome >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
                {{ data.netIncome >= 0 ? '✅ ربح' : '❌ خسارة' }}
            </p>
        </div>
    </div>
</template>
