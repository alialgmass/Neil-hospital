<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Activity, AlertTriangle, Banknote, Users } from 'lucide-vue-next';
import Badge from '@/components/shared/Badge.vue';
import StatCard from '@/components/shared/StatCard.vue';

interface TodayStats {
    today_bookings: number;
    today_revenue:  number;
    today_paid:     number;
    today_pending:  number;
}
interface DeptRow  { dept: string; cases: number; revenue: number }
interface DocRow   { doctor_name: string; cases: number; revenue: number }
interface Treasury { total_in: number; total_out: number; balance: number }
interface QueueEntry { id: string; file_no: string; patient_name: string; dept: string; status: string; pay_status: string; time?: string; doctor_name?: string }

defineProps<{
    todayStats:    TodayStats;
    revenueByDept: DeptRow[];
    revenueByDoc:  DocRow[];
    treasury:      Treasury;
    lowStockCount: number;
    todayQueue:    QueueEntry[];
    filters:       { from?: string; to?: string };
}>();

const deptLabels: Record<string, string> = {
    clinic: 'عيادة', labs: 'فحوصات', surgery: 'عمليات', lasik: 'ليزك', laser: 'ليزر',
};
function fmt(n: number) {
 return Number(n).toLocaleString('ar-EG') + ' ج.م'; 
}
</script>

<template>
    <Head title="لوحة التحكم" />

    <!-- Today stats -->
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <StatCard label="حجوزات اليوم" :value="String(todayStats.today_bookings)" color="primary">
            <template #icon><Users class="h-5 w-5" /></template>
        </StatCard>
        <StatCard label="إيرادات اليوم" :value="fmt(todayStats.today_revenue)" color="success">
            <template #icon><Banknote class="h-5 w-5" /></template>
        </StatCard>
        <StatCard label="مسدد اليوم" :value="String(todayStats.today_paid)" color="accent">
            <template #icon><Activity class="h-5 w-5" /></template>
        </StatCard>
        <StatCard label="رصيد الخزنة" :value="fmt(treasury.balance)" :color="treasury.balance >= 0 ? 'primary' : 'danger'">
            <template #icon><Banknote class="h-5 w-5" /></template>
        </StatCard>
    </div>

    <!-- Low stock alert -->
    <div v-if="lowStockCount > 0" class="mb-5 flex items-center gap-2 rounded-xl border border-hospital-warning/30 bg-hospital-warning/10 px-4 py-3 text-hospital-warning">
        <AlertTriangle class="h-5 w-5" />
        <span class="text-sm font-medium">تحذير: {{ lowStockCount }} صنف في المخزن وصل للحد الأدنى</span>
        <a href="/inventory?low_stock=1" class="mr-auto text-xs underline">عرض الأصناف</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Today's queue -->
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-hospital-border bg-white p-5 shadow-sm">
                <h3 class="mb-4 font-semibold text-hospital-text">قائمة انتظار اليوم</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-hospital-border text-xs text-hospital-text-2">
                                <th class="pb-2 text-right font-medium">الوقت</th>
                                <th class="pb-2 text-right font-medium">رقم الملف</th>
                                <th class="pb-2 text-right font-medium">المريض</th>
                                <th class="pb-2 text-right font-medium">القسم</th>
                                <th class="pb-2 text-right font-medium">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="entry in todayQueue" :key="entry.id" class="border-b border-hospital-border/50 hover:bg-hospital-bg/40">
                                <td class="py-2.5 font-mono text-xs">{{ entry.time?.slice(0, 5) ?? '—' }}</td>
                                <td class="py-2.5 font-mono text-xs">{{ entry.file_no }}</td>
                                <td class="py-2.5">{{ entry.patient_name }}</td>
                                <td class="py-2.5 text-xs text-hospital-text-2">{{ deptLabels[entry.dept] ?? entry.dept }}</td>
                                <td class="py-2.5">
                                    <Badge :variant="(entry.status as 'confirmed' | 'waiting' | 'in_progress')" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="todayQueue.length === 0" class="py-8 text-center text-hospital-text-2">لا توجد حجوزات اليوم</p>
                </div>
            </div>
        </div>

        <!-- Revenue by dept -->
        <div class="space-y-4">
            <div class="rounded-xl border border-hospital-border bg-white p-5 shadow-sm">
                <h3 class="mb-4 font-semibold text-hospital-text">إيرادات الشهر بالقسم</h3>
                <div class="space-y-3">
                    <div v-for="row in revenueByDept" :key="row.dept" class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-hospital-text">{{ deptLabels[row.dept] ?? row.dept }}</p>
                            <p class="text-xs text-hospital-text-2">{{ row.cases }} حالة</p>
                        </div>
                        <span class="font-mono text-sm font-semibold text-hospital-primary">{{ fmt(row.revenue) }}</span>
                    </div>
                    <p v-if="revenueByDept.length === 0" class="py-4 text-center text-sm text-hospital-text-2">لا توجد بيانات</p>
                </div>
            </div>

            <div class="rounded-xl border border-hospital-border bg-white p-5 shadow-sm">
                <h3 class="mb-4 font-semibold text-hospital-text">إيرادات الشهر بالطبيب</h3>
                <div class="space-y-3">
                    <div v-for="row in revenueByDoc" :key="row.doctor_name" class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-hospital-text">{{ row.doctor_name }}</p>
                            <p class="text-xs text-hospital-text-2">{{ row.cases }} حالة</p>
                        </div>
                        <span class="font-mono text-sm font-semibold text-hospital-accent">{{ fmt(row.revenue) }}</span>
                    </div>
                    <p v-if="revenueByDoc.length === 0" class="py-4 text-center text-sm text-hospital-text-2">لا توجد بيانات</p>
                </div>
            </div>
        </div>
    </div>
</template>
