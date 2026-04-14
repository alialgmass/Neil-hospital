<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Printer } from 'lucide-vue-next';
import { ref } from 'vue';

interface AccountRow {
    code: string;
    name: string;
    balance: number;
}

interface Statement {
    revenues: AccountRow[];
    expenses: AccountRow[];
    totalRevenue: number;
    totalExpense: number;
    netIncome: number;
}

const props = defineProps<{
    statement: Statement;
    filters: { from?: string; to?: string };
}>();

const fromFilter = ref(props.filters.from ?? '');
const toFilter   = ref(props.filters.to   ?? '');

function applyFilters() {
    router.get('/ledger/income-statement', {
        from: fromFilter.value || undefined,
        to:   toFilter.value   || undefined,
    }, { preserveState: true });
}

function fmt(n: number) {
    return n.toLocaleString('ar-EG', { minimumFractionDigits: 2 });
}
function printPage() {
 window.print(); 
}
</script>

<template>
    <Head title="قائمة الدخل" />

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <h2 class="text-lg font-bold text-hospital-text">قائمة الدخل (الأرباح والخسائر)</h2>
        <div class="flex items-center gap-2">
            <input v-model="fromFilter" type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <input v-model="toFilter"   type="date" class="rounded-lg border border-hospital-border bg-hospital-bg px-3 py-2 text-sm focus:border-hospital-primary focus:outline-none" @change="applyFilters" />
            <button class="flex items-center gap-1.5 rounded-lg border border-hospital-border px-3 py-2 text-sm hover:bg-hospital-bg print:hidden" @click="printPage">
                <Printer class="h-4 w-4" /> طباعة
            </button>
        </div>
    </div>

    <div class="mx-auto max-w-2xl space-y-4">
        <!-- Revenues -->
        <div class="overflow-hidden rounded-xl border border-hospital-border bg-white shadow-sm">
            <div class="border-b border-hospital-border bg-green-50 px-4 py-3">
                <h3 class="font-semibold text-green-800">الإيرادات</h3>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <tr
                        v-for="row in statement.revenues"
                        :key="row.code"
                        class="border-b border-hospital-border/50 hover:bg-hospital-bg/40"
                    >
                        <td class="px-4 py-2.5 text-hospital-text-2 font-mono">{{ row.code }}</td>
                        <td class="px-4 py-2.5 text-hospital-text">{{ row.name }}</td>
                        <td class="px-4 py-2.5 text-left font-mono text-green-700 font-medium">{{ fmt(row.balance) }}</td>
                    </tr>
                    <tr v-if="statement.revenues.length === 0">
                        <td colspan="3" class="px-4 py-3 text-center text-hospital-muted">لا توجد إيرادات</td>
                    </tr>
                </tbody>
                <tfoot class="border-t border-green-200 bg-green-50 font-semibold">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-green-800">إجمالي الإيرادات</td>
                        <td class="px-4 py-3 text-left font-mono text-green-800">{{ fmt(statement.totalRevenue) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Expenses -->
        <div class="overflow-hidden rounded-xl border border-hospital-border bg-white shadow-sm">
            <div class="border-b border-hospital-border bg-red-50 px-4 py-3">
                <h3 class="font-semibold text-red-800">المصروفات</h3>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <tr
                        v-for="row in statement.expenses"
                        :key="row.code"
                        class="border-b border-hospital-border/50 hover:bg-hospital-bg/40"
                    >
                        <td class="px-4 py-2.5 text-hospital-text-2 font-mono">{{ row.code }}</td>
                        <td class="px-4 py-2.5 text-hospital-text">{{ row.name }}</td>
                        <td class="px-4 py-2.5 text-left font-mono text-red-700 font-medium">{{ fmt(row.balance) }}</td>
                    </tr>
                    <tr v-if="statement.expenses.length === 0">
                        <td colspan="3" class="px-4 py-3 text-center text-hospital-muted">لا توجد مصروفات</td>
                    </tr>
                </tbody>
                <tfoot class="border-t border-red-200 bg-red-50 font-semibold">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-red-800">إجمالي المصروفات</td>
                        <td class="px-4 py-3 text-left font-mono text-red-800">{{ fmt(statement.totalExpense) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Net Income -->
        <div
            class="overflow-hidden rounded-xl border-2 shadow-sm"
            :class="statement.netIncome >= 0 ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50'"
        >
            <div class="flex items-center justify-between px-6 py-4">
                <span
                    class="text-lg font-bold"
                    :class="statement.netIncome >= 0 ? 'text-green-800' : 'text-red-800'"
                >
                    {{ statement.netIncome >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}
                </span>
                <span
                    class="text-xl font-bold font-mono"
                    :class="statement.netIncome >= 0 ? 'text-green-700' : 'text-red-700'"
                >
                    {{ fmt(Math.abs(statement.netIncome)) }} ج
                </span>
            </div>
        </div>
    </div>
</template>
